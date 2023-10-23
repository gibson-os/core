<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class EventRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Element::class)]
        private readonly string $eventElementTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getById(int $id): Event
    {
        return $this->fetchOne('`id`=?', [$id], Event::class);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
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
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return Event[]
     */
    public function getTimeControlled(string $className, string $trigger, DateTimeInterface $dateTime): array
    {
        $query = $this->getSelectQuery($this->eventElementTableName, 'ee')
            ->setOrder('`et`.`priority`', OrderDirection::DESC)
            ->setOrder('`ee`.`parentId`')
            ->setOrder('`ee`.`order`')
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

        return $this->getModels(
            $query,
            Event::class,
            'event_',
            [
                new ChildrenMapping('triggers', 'trigger_', 'et'),
                new ChildrenMapping('elements', 'element_', 'ee'),
            ],
        );
    }
}
