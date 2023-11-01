<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTime;
use DateTimeImmutable;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\DateTimeService;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;

class EventRepositoryTest extends Unit
{
    use RepositoryTrait;

    private EventRepository $eventRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private Table $eventElementTable;

    private Table $eventTriggerTable;

    protected function _before()
    {
        $this->loadRepository('event');

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->eventElementTable = new Table('event_element', []);
        $this->eventTriggerTable = new Table('event_trigger', []);

        $this->eventRepository = new EventRepository(
            $this->repositoryWrapper->reveal(),
            'event_element',
        );
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Event::class);
        $event = $this->eventRepository->getById(42);

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $event->setModified($date);

        $this->assertEquals($model, $event);
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name` LIKE ?', ['galaxy%']))
        ;

        $model = $this->loadModel($selectQuery, Event::class, '');
        $event = $this->eventRepository->findByName('galaxy', false)[0];

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $event->setModified($date);

        $this->assertEquals($model, $event);
    }

    public function testFindByNameOnlyActive(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`name` LIKE ? AND `active`=?', ['galaxy%', 1]))
        ;

        $model = $this->loadModel($selectQuery, Event::class, '');
        $event = $this->eventRepository->findByName('galaxy', true)[0];

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $event->setModified($date);

        $this->assertEquals($model, $event);
    }

    public function testGetTimeControlled(): void
    {
        $date = new DateTime();
        $selectQuery = (new SelectQuery($this->eventElementTable, 'ee'))
            ->setOrder('`et`.`priority`', OrderDirection::DESC)
            ->setOrder('`ee`.`parentId`')
            ->setOrder('`ee`.`order`')
            ->addWhere(new Where('`e`.`active`=?', [1]))
            ->addWhere(new Where('`et`.`class`=?', ['arthur']))
            ->addWhere(new Where('`et`.`trigger`=?', ['dent']))
            ->addWhere(new Where('`et`.`weekday` IS NULL OR `et`.`weekday`=?', [(int) $date->format('w')]))
            ->addWhere(new Where('`et`.`day` IS NULL OR `et`.`day`=?', [(int) $date->format('j')]))
            ->addWhere(new Where('`et`.`month` IS NULL OR `et`.`month`=?', [(int) $date->format('n')]))
            ->addWhere(new Where('`et`.`year` IS NULL OR `et`.`year`=?', [(int) $date->format('Y')]))
            ->addWhere(new Where('`et`.`hour` IS NULL OR `et`.`hour`=?', [(int) $date->format('H')]))
            ->addWhere(new Where('`et`.`minute` IS NULL OR `et`.`minute`=?', [(int) $date->format('i')]))
            ->addWhere(new Where('`et`.`second` IS NULL OR `et`.`second`=?', [(int) $date->format('s')]))
        ;

        $this->tableManager->getTable($this->eventElementTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->eventElementTable)
        ;
        $this->childrenQuery->extend(
            $selectQuery,
            Event::class,
            [
                new ChildrenMapping('triggers', 'trigger_', 'et'),
                new ChildrenMapping('elements', 'element_', 'ee'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn($selectQuery)
        ;

        $model = $this->loadModel($selectQuery, Event::class, 'event_');

        $this->tableManager->getTable($this->table->getTableName())
            ->shouldNotBeCalled()
        ;
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->primaryKeyExtractor->extractFromRecord($this->eventElementTable, new Record([]), 'event_')
            ->willReturn([])
        ;

        $event = $this->eventRepository->getTimeControlled('arthur', 'dent', $date)[0];

        $date = new DateTimeImmutable();
        $model->setModified($date);
        $event->setModified($date);

        $this->assertEquals($model, $event);
    }
}
