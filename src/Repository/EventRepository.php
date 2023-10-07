<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use Generator;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Dto\Event\Command;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\RepositoryService;
use GibsonOS\Core\Utility\JsonUtility;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Select;
use MDO\Dto\Table;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;
use stdClass;

class EventRepository extends AbstractRepository
{
    public function __construct(
        RepositoryService $repositoryService,
        private readonly DateTimeService $dateTimeService,
        #[GetTable(Event::class)]
        private Table $eventTable,
        #[GetTable(Element::class)]
        private Table $eventElementTable,
        #[GetTable(Trigger::class)]
        private Table $eventTriggerTable,
    ) {
        parent::__construct($repositoryService);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(int $id): Event
    {
        return $this->fetchOne('`id`=?', [$id], Event::class);
    }

    /**
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<Event>
     */
    public function findByName(string $name, bool $onlyActive): Generator
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($onlyActive) {
            $where .= ' AND `active`=?';
            $parameters[] = 1;
        }

        yield from $this->fetchAll($where, $parameters, Event::class);
    }

    /**
     * @return Event[]
     */
    public function getTimeControlled(string $className, string $trigger, DateTimeInterface $dateTime): array
    {
        $query = $this->initializeQuery()
            ->addWhere(new Where('`e`.`active`=?', [1]))
            ->addWhere(new Where('`et`.`class`=?', [$className]))
            ->addWhere(new Where('`et`.`trigger`=?', [$trigger]))
            ->addWhere(new Where('`et`.`weekday` IS NULL OR `et`.`weekday`=?', [(int) $dateTime->format('w')]))
            ->addWhere(new Where('`et`.`day` IS NULL OR `et`.`day`=?', [(int) $dateTime->format('j')]))
            ->addWhere(new Where('`et`.`month` IS NULL OR `et`.`month`=?', [(int) $dateTime->format('n')]))
            ->addWhere(new Where('`et`.`year` IS NULL OR `et`.`year`=?', [(int) $dateTime->format('Y')]))
            ->addWhere(new Where('`et`.`hour` IS NULL OR `et`.`hour`=?', [(int) $dateTime->format('H')]))
            ->addWhere(new Where('`et`.`minute` IS NULL OR `et`.`minute`=?', [(int) $dateTime->format('i')]))
            ->addWhere(new Where('`et`.`second` IS NULL OR `et`.`second`=?', [(int) $dateTime->format('s')]))
        ;

        if (!$table->selectPrepared(false)) {
            return [];
        }

        return $this->matchModels($table->connection->fetchObjectList());
    }

    private function initializeQuery(): SelectQuery
    {
        $selectQuery = $this->getSelectQuery($this->eventElementTable->getTableName(), 'ee')
            ->addJoin(new Join($this->eventTable, 'e', '`e`.`id`=`ee`.`event_id`', JoinType::LEFT))
            ->addJoin(new Join($this->eventTriggerTable, 'et', '`e`.`id`=`et`.`event_id`', JoinType::LEFT))
            ->setOrder('`et`.`priority`', OrderDirection::DESC)
            ->setOrder('`ee`.`parentId`')
            ->setOrder('`ee`.`order`')
            ->setSelects($this->repositoryService->getSelectService()->getSelects([
                new Select($this->eventTable, 'e', 'event_'),
                new Select($this->eventElementTable, 'ee', 'element_'),
                new Select($this->eventTriggerTable, 'et', 'trigger_'),
            ]))
        ;

        return $selectQuery;
    }

    /**
     * @param stdClass[] $events
     *
     * @return Event[]
     */
    private function matchModels(array $events): array
    {
        /** @var Event[] */
        $models = [];
        /** @var Trigger[] $triggerModels */
        $triggerModels = [];
        /** @var Element[] $elementModels */
        $elementModels = [];

        foreach ($events as $event) {
            if (!isset($models[$event->id])) {
                $models[$event->id] = (new Event())
                    ->setId((int) $event->id)
                    ->setName($event->name)
                    ->setActive((bool) $event->active)
                    ->setAsync((bool) $event->async)
                    ->setModified($this->dateTimeService->get($event->modified))
                    ->setTriggers([])
                    ->setElements([])
                ;
            }

            if (!isset($triggerModels[$event->triggerId])) {
                $triggerModel = (new Trigger())
                    ->setId((int) $event->triggerId)
                    ->setEvent($models[$event->id])
                    ->setClass($event->triggerClass)
                    ->setTrigger($event->triggerTrigger)
                    ->setParameters(JsonUtility::decode($event->triggerParameters))
                    ->setWeekday((int) $event->triggerWeekday ?: null)
                    ->setDay((int) $event->triggerDay ?: null)
                    ->setMonth((int) $event->triggerMonth ?: null)
                    ->setYear((int) $event->triggerYear ?: null)
                    ->setHour((int) $event->triggerHour ?: null)
                    ->setMinute((int) $event->triggerMinute ?: null)
                    ->setPriority((int) $event->triggerPriority ?: null)
                ;
                $models[$event->id]->addTriggers([$triggerModel]);
                $triggerModels[$event->triggerId] = $triggerModel;
            }

            $elementModel = (new Element())
                ->setId((int) $event->elementId)
                ->setEvent($models[$event->id])
                ->setParent($event->elementParentId === null ? null : $elementModels[$event->elementParentId])
                ->setOrder($event->elementOrder)
                ->setClass($event->elementClass)
                ->setMethod($event->elementMethod)
                ->setParameters(JsonUtility::decode($event->elementParameters))
                ->setCommand($event->elementCommand === null ? null : Command::from($event->elementCommand))
                ->setReturns(JsonUtility::decode($event->elementReturns))
                ->setChildren([])
            ;

            if ($event->elementParentId === null) {
                $models[$event->id]->addElements([$elementModel]);
            } else {
                $elementModels[$event->elementParentId]->addChildren([$elementModel]);
            }

            $elementModels[$event->elementId] = $elementModel;
        }

        return array_values($models);
    }
}
