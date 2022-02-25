<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Dto\Event\Command;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\DeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use mysqlTable;
use stdClass;

class EventRepository extends AbstractRepository
{
    public function __construct(
        private JsonUtility $jsonUtility,
        private DateTimeService $dateTimeService,
        #[GetTableName(Element::class)] private string $elementTableName,
        #[GetTableName(Trigger::class)] private string $triggerTableName
    ) {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Event
    {
        return $this->fetchOne('`id`=?', [$id], Event::class);
    }

    /**
     * @throws SelectError
     *
     * @return Event[]
     */
    public function findByName(string $name, bool $onlyActive): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($onlyActive) {
            $where .= ' AND `active`=?';
            $parameters[] = 1;
        }

        return $this->fetchAll($where, $parameters, Event::class);
    }

    /**
     * @return Event[]
     */
    public function getTimeControlled(string $className, string $trigger, DateTimeInterface $dateTime): array
    {
        $table = $this->initializeTable()
            ->setWhere(
                '`event`.`active`=? AND ' .
                '`event_trigger`.`class`=? AND ' .
                '`event_trigger`.`trigger`=? AND ' .
                '(`event_trigger`.`weekday` IS NULL OR `event_trigger`.`weekday`=?) AND ' .
                '(`event_trigger`.`day` IS NULL OR `event_trigger`.`day`=?) AND ' .
                '(`event_trigger`.`month` IS NULL OR `event_trigger`.`month`=?) AND ' .
                '(`event_trigger`.`year` IS NULL OR `event_trigger`.`year`=?) AND ' .
                '(`event_trigger`.`hour` IS NULL OR `event_trigger`.`hour`=?) AND ' .
                '(`event_trigger`.`minute` IS NULL OR `event_trigger`.`minute`=?) AND ' .
                '(`event_trigger`.`second` IS NULL OR `event_trigger`.`second`=?)'
            )
            ->setWhereParameters([
                1,
                $className,
                $trigger,
                (int) $dateTime->format('w'),
                (int) $dateTime->format('j'),
                (int) $dateTime->format('n'),
                (int) $dateTime->format('Y'),
                (int) $dateTime->format('H'),
                (int) $dateTime->format('i'),
                (int) $dateTime->format('s'),
            ]);

        if (!$table->selectPrepared(false)) {
            return [];
        }

        return $this->matchModels($table->connection->fetchObjectList());
    }

    private function initializeTable(): mysqlTable
    {
        $table = $this->getTable($this->elementTableName);
        $table->appendJoinLeft('`event`', '`event_element`.`event_id`=`event`.`id`');
        $table->appendJoinLeft('`event_trigger`', '`event_element`.`event_id`=`event_trigger`.`event_id`');
        $table->setOrderBy('`event_trigger`.`priority` DESC, `event_element`.`parent_id`, `event_element`.`order`');
        $table->setSelectString(
            '`event`.`id`, ' .
            '`event`.`name`, ' .
            '`event`.`active`, ' .
            '`event`.`async`, ' .
            '`event`.`modified`, ' .
            '`event_element`.`id` AS `elementId`, ' .
            '`event_element`.`parent_id` AS `elementParentId`, ' .
            '`event_element`.`order` AS `elementOrder`, ' .
            '`event_element`.`class` AS `elementClass`, ' .
            '`event_element`.`method` AS `elementMethod`, ' .
            '`event_element`.`parameters` AS `elementParameters`, ' .
            '`event_element`.`command` AS `elementCommand`, ' .
            '`event_element`.`returns` AS `elementReturns`, ' .
            '`event_trigger`.`id` AS `triggerId`, ' .
            '`event_trigger`.`class` AS `triggerClass`, ' .
            '`event_trigger`.`trigger` AS `triggerTrigger`, ' .
            '`event_trigger`.`parameters` AS `triggerParameters`, ' .
            '`event_trigger`.`weekday` AS `triggerWeekday`, ' .
            '`event_trigger`.`day` AS `triggerDay`, ' .
            '`event_trigger`.`month` AS `triggerMonth`, ' .
            '`event_trigger`.`year` AS `triggerYear`, ' .
            '`event_trigger`.`hour` AS `triggerHour`, ' .
            '`event_trigger`.`minute` AS `triggerMinute`, ' .
            '`event_trigger`.`priority` AS `triggerPriority`'
        );

        return $table;
    }

    /**
     * @param int[]|null $notIds
     *
     * @throws DeleteError
     */
    public function deleteElements(Event $event, ?array $notIds): void
    {
        $table = $this->getTable($this->elementTableName)
            ->setWhere(
                (
                    empty($notIds)
                    ? ''
                    : '`id` NOT IN (' . implode(', ', array_fill(0, count($notIds), '?')) . ') AND '
                ) . '`event_id`=?'
            )
        ;

        if (!empty($notIds)) {
            $table->setWhereParameters($notIds);
        }

        $table->addWhereParameter($event->getId());

        if (!$table->deletePrepared()) {
            throw (new DeleteError())->setTable($table);
        }
    }

    /**
     * @param int[] $elementIds
     *
     * @throws SaveError
     * @throws JsonException
     *
     * @return int[]
     */
    public function saveElements(Event $event, array $elements, int $parentId = null, array $elementIds = []): array
    {
        $order = 0;

        foreach ($elements as $element) {
            $elementModel = (new Event\Element())
                ->setId($element['id'])
                ->setEvent($event)
                ->setParentId($parentId)
                ->setClass($element['className'])
                ->setMethod($element['method'])
                ->setCommand($element['command'] === null ? null : Command::from($element['command']))
                ->setParameters($element['parameters'] ?? [])
                ->setReturns($element['returns'] ?? [])
                ->setOrder($order++)
            ;
            $elementModel->save();
            $elementIds[] = $elementModel->getId() ?? 0;
            $elementIds = $this->saveElements($event, $element['children'], $elementModel->getId() ?? 0, $elementIds);
        }

        return $elementIds;
    }

    /**
     * @param int[]|null $notIds
     *
     * @throws DeleteError
     */
    public function deleteTriggers(Event $event, ?array $notIds): void
    {
        $table = $this->getTable($this->triggerTableName)
            ->setWhere(
                (
                    empty($notIds)
                    ? ''
                    : '`id` NOT IN (' . implode(', ', array_fill(0, count($notIds), '?')) . ') AND '
                ) . '`event_id`=?'
            )
        ;

        if (!empty($notIds)) {
            $table->setWhereParameters($notIds);
        }

        $table->addWhereParameter($event->getId());

        if (!$table->deletePrepared()) {
            throw (new DeleteError())->setTable($table);
        }
    }

    /**
     * @throws JsonException
     * @throws SaveError
     *
     * @return int[]
     */
    public function saveTriggers(Event $event, array $triggers): array
    {
        $triggerIds = [];

        foreach ($triggers as $priority => $trigger) {
            $triggerModel = (new Trigger())
                ->setId($trigger['id'])
                ->setEvent($event)
                ->setClass($trigger['className'])
                ->setParameters(empty($trigger['parameters']) ? null : $this->jsonUtility->encode($trigger['parameters']))
                ->setTrigger($trigger['trigger'])
                ->setWeekday($trigger['weekday'])
                ->setDay($trigger['day'])
                ->setMonth($trigger['month'])
                ->setYear($trigger['year'])
                ->setHour($trigger['hour'])
                ->setMinute($trigger['minute'])
                ->setSecond($trigger['second'])
                ->setPriority($priority)
            ;
            $triggerModel->save();
            $triggerIds[] = $triggerModel->getId() ?? 0;
        }

        return $triggerIds;
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
                    ->setParameters($event->triggerParameters)
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
                ->setParameters($event->elementParameters)
                ->setCommand($event->elementCommand === null ? null : Command::from($event->elementCommand))
                ->setReturns($event->elementReturns)
                ->setChildren([])
            ;

            if ($event->elementParentId === null) {
                $models[$event->id]->addElements([$elementModel]);
            } else {
                $elementModels[$event->elementParentId]->addChildren([$elementModel]);
            }

            $elementModels[$event->elementId] = $elementModel;
        }

        return $models;
    }
}
