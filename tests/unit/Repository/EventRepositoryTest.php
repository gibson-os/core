<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

class EventRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private EventRepository $eventRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['name', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->eventRepository = new EventRepository(
            $this->dateTimeService->reveal(),
            'event_element',
        );
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`name` FROM `marvin`.`event` WHERE `id`=? LIMIT 1',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $event = $this->eventRepository->getById(42);

        $this->assertEquals('marvin', $event->getName());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`name` FROM `marvin`.`event` WHERE `name` LIKE ?',
            ['galaxy%'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $event = $this->eventRepository->findByName('galaxy', false)[0];

        $this->assertEquals('marvin', $event->getName());
    }

    public function testFindByNameOnlyActive(): void
    {
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`name` FROM `marvin`.`event` WHERE `name` LIKE ? AND `active`=?',
            ['galaxy%', 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
            ]])
        ;

        $event = $this->eventRepository->findByName('galaxy', true)[0];

        $this->assertEquals('marvin', $event->getName());
    }

    public function testGetTimeControlled(): void
    {
        $date = new DateTime();

        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event_element`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`id`, `event`.`name`, `event`.`active`, `event`.`async`, `event`.`modified`, `event_element`.`id` AS `elementId`, `event_element`.`parent_id` AS `elementParentId`, `event_element`.`order` AS `elementOrder`, `event_element`.`class` AS `elementClass`, `event_element`.`method` AS `elementMethod`, `event_element`.`parameters` AS `elementParameters`, `event_element`.`command` AS `elementCommand`, `event_element`.`returns` AS `elementReturns`, `event_trigger`.`id` AS `triggerId`, `event_trigger`.`class` AS `triggerClass`, `event_trigger`.`trigger` AS `triggerTrigger`, `event_trigger`.`parameters` AS `triggerParameters`, `event_trigger`.`weekday` AS `triggerWeekday`, `event_trigger`.`day` AS `triggerDay`, `event_trigger`.`month` AS `triggerMonth`, `event_trigger`.`year` AS `triggerYear`, `event_trigger`.`hour` AS `triggerHour`, `event_trigger`.`minute` AS `triggerMinute`, `event_trigger`.`priority` AS `triggerPriority` FROM `marvin`.`event_element` LEFT JOIN `event` ON `event_element`.`event_id`=`event`.`id` LEFT JOIN `event_trigger` ON `event_element`.`event_id`=`event_trigger`.`event_id` WHERE `event`.`active`=? AND `event_trigger`.`class`=? AND `event_trigger`.`trigger`=? AND (`event_trigger`.`weekday` IS NULL OR `event_trigger`.`weekday`=?) AND (`event_trigger`.`day` IS NULL OR `event_trigger`.`day`=?) AND (`event_trigger`.`month` IS NULL OR `event_trigger`.`month`=?) AND (`event_trigger`.`year` IS NULL OR `event_trigger`.`year`=?) AND (`event_trigger`.`hour` IS NULL OR `event_trigger`.`hour`=?) AND (`event_trigger`.`minute` IS NULL OR `event_trigger`.`minute`=?) AND (`event_trigger`.`second` IS NULL OR `event_trigger`.`second`=?) ORDER BY `event_trigger`.`priority` DESC, `event_element`.`parent_id`, `event_element`.`order`',
            [
                1,
                'arthur',
                'dent',
                (int) $date->format('w'),
                (int) $date->format('j'),
                (int) $date->format('n'),
                (int) $date->format('Y'),
                (int) $date->format('H'),
                (int) $date->format('i'),
                (int) $date->format('s'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $object = new stdClass();
        $object->id = 42;
        $object->active = 1;
        $object->async = 1;
        $object->name = 'marvin';
        $object->modified = 'zaphod';
        $object->triggerId = 21;
        $object->triggerClass = 'ford';
        $object->triggerTrigger = 'prefect';
        $object->triggerParameters = '[]';
        $object->triggerWeekday = 0;
        $object->triggerDay = 0;
        $object->triggerMonth = 0;
        $object->triggerYear = 0;
        $object->triggerHour = 0;
        $object->triggerMinute = 0;
        $object->triggerPriority = 0;
        $object->elementId = 7;
        $object->elementParentId = null;
        $object->elementOrder = 0;
        $object->elementClass = 'arthur';
        $object->elementMethod = 'dent';
        $object->elementParameters = '[]';
        $object->elementCommand = null;
        $object->elementReturns = '[]';

        $this->dateTimeService->get('zaphod')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime())
        ;
        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$object])
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event_element`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $event = $this->eventRepository->getTimeControlled('arthur', 'dent', $date)[0];

        $this->assertEquals('marvin', $event->getName());
        $this->assertEquals([], $event->getElements()[0]->getChildren());
    }

    public function testGetTimeControlledWithChildren(): void
    {
        $date = new DateTime();

        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event_element`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`id`, `event`.`name`, `event`.`active`, `event`.`async`, `event`.`modified`, `event_element`.`id` AS `elementId`, `event_element`.`parent_id` AS `elementParentId`, `event_element`.`order` AS `elementOrder`, `event_element`.`class` AS `elementClass`, `event_element`.`method` AS `elementMethod`, `event_element`.`parameters` AS `elementParameters`, `event_element`.`command` AS `elementCommand`, `event_element`.`returns` AS `elementReturns`, `event_trigger`.`id` AS `triggerId`, `event_trigger`.`class` AS `triggerClass`, `event_trigger`.`trigger` AS `triggerTrigger`, `event_trigger`.`parameters` AS `triggerParameters`, `event_trigger`.`weekday` AS `triggerWeekday`, `event_trigger`.`day` AS `triggerDay`, `event_trigger`.`month` AS `triggerMonth`, `event_trigger`.`year` AS `triggerYear`, `event_trigger`.`hour` AS `triggerHour`, `event_trigger`.`minute` AS `triggerMinute`, `event_trigger`.`priority` AS `triggerPriority` FROM `marvin`.`event_element` LEFT JOIN `event` ON `event_element`.`event_id`=`event`.`id` LEFT JOIN `event_trigger` ON `event_element`.`event_id`=`event_trigger`.`event_id` WHERE `event`.`active`=? AND `event_trigger`.`class`=? AND `event_trigger`.`trigger`=? AND (`event_trigger`.`weekday` IS NULL OR `event_trigger`.`weekday`=?) AND (`event_trigger`.`day` IS NULL OR `event_trigger`.`day`=?) AND (`event_trigger`.`month` IS NULL OR `event_trigger`.`month`=?) AND (`event_trigger`.`year` IS NULL OR `event_trigger`.`year`=?) AND (`event_trigger`.`hour` IS NULL OR `event_trigger`.`hour`=?) AND (`event_trigger`.`minute` IS NULL OR `event_trigger`.`minute`=?) AND (`event_trigger`.`second` IS NULL OR `event_trigger`.`second`=?) ORDER BY `event_trigger`.`priority` DESC, `event_element`.`parent_id`, `event_element`.`order`',
            [
                1,
                'arthur',
                'dent',
                (int) $date->format('w'),
                (int) $date->format('j'),
                (int) $date->format('n'),
                (int) $date->format('Y'),
                (int) $date->format('H'),
                (int) $date->format('i'),
                (int) $date->format('s'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $object1 = new stdClass();
        $object1->id = 42;
        $object1->active = 1;
        $object1->async = 1;
        $object1->name = 'marvin';
        $object1->modified = 'zaphod';
        $object1->triggerId = 21;
        $object1->triggerClass = 'ford';
        $object1->triggerTrigger = 'prefect';
        $object1->triggerParameters = '[]';
        $object1->triggerWeekday = 0;
        $object1->triggerDay = 0;
        $object1->triggerMonth = 0;
        $object1->triggerYear = 0;
        $object1->triggerHour = 0;
        $object1->triggerMinute = 0;
        $object1->triggerPriority = 0;
        $object1->elementId = 7;
        $object1->elementParentId = null;
        $object1->elementOrder = 0;
        $object1->elementClass = 'arthur';
        $object1->elementMethod = 'dent';
        $object1->elementParameters = '[]';
        $object1->elementCommand = null;
        $object1->elementReturns = '[]';

        $object2 = new stdClass();
        $object2->id = 42;
        $object2->active = 1;
        $object2->async = 1;
        $object2->name = 'marvin';
        $object2->modified = 'zaphod';
        $object2->triggerId = 21;
        $object2->triggerClass = 'ford';
        $object2->triggerTrigger = 'prefect';
        $object2->triggerParameters = '[]';
        $object2->triggerWeekday = 0;
        $object2->triggerDay = 0;
        $object2->triggerMonth = 0;
        $object2->triggerYear = 0;
        $object2->triggerHour = 0;
        $object2->triggerMinute = 0;
        $object2->triggerPriority = 0;
        $object2->elementId = 3;
        $object2->elementParentId = 7;
        $object2->elementOrder = 0;
        $object2->elementClass = 'arthur';
        $object2->elementMethod = 'dent';
        $object2->elementParameters = '[]';
        $object2->elementCommand = null;
        $object2->elementReturns = '[]';

        $this->dateTimeService->get('zaphod')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime())
        ;
        $this->mysqlDatabase->fetchObjectList()
            ->shouldBeCalledOnce()
            ->willReturn([$object1, $object2])
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event_element`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $event = $this->eventRepository->getTimeControlled('arthur', 'dent', $date)[0];

        $this->assertEquals('marvin', $event->getName());
        $this->assertCount(1, $event->getElements()[0]->getChildren());
    }

    public function testGetTimeControlledEmpty(): void
    {
        $date = new DateTime();

        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`event_element`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->execute(
            'SELECT `event`.`id`, `event`.`name`, `event`.`active`, `event`.`async`, `event`.`modified`, `event_element`.`id` AS `elementId`, `event_element`.`parent_id` AS `elementParentId`, `event_element`.`order` AS `elementOrder`, `event_element`.`class` AS `elementClass`, `event_element`.`method` AS `elementMethod`, `event_element`.`parameters` AS `elementParameters`, `event_element`.`command` AS `elementCommand`, `event_element`.`returns` AS `elementReturns`, `event_trigger`.`id` AS `triggerId`, `event_trigger`.`class` AS `triggerClass`, `event_trigger`.`trigger` AS `triggerTrigger`, `event_trigger`.`parameters` AS `triggerParameters`, `event_trigger`.`weekday` AS `triggerWeekday`, `event_trigger`.`day` AS `triggerDay`, `event_trigger`.`month` AS `triggerMonth`, `event_trigger`.`year` AS `triggerYear`, `event_trigger`.`hour` AS `triggerHour`, `event_trigger`.`minute` AS `triggerMinute`, `event_trigger`.`priority` AS `triggerPriority` FROM `marvin`.`event_element` LEFT JOIN `event` ON `event_element`.`event_id`=`event`.`id` LEFT JOIN `event_trigger` ON `event_element`.`event_id`=`event_trigger`.`event_id` WHERE `event`.`active`=? AND `event_trigger`.`class`=? AND `event_trigger`.`trigger`=? AND (`event_trigger`.`weekday` IS NULL OR `event_trigger`.`weekday`=?) AND (`event_trigger`.`day` IS NULL OR `event_trigger`.`day`=?) AND (`event_trigger`.`month` IS NULL OR `event_trigger`.`month`=?) AND (`event_trigger`.`year` IS NULL OR `event_trigger`.`year`=?) AND (`event_trigger`.`hour` IS NULL OR `event_trigger`.`hour`=?) AND (`event_trigger`.`minute` IS NULL OR `event_trigger`.`minute`=?) AND (`event_trigger`.`second` IS NULL OR `event_trigger`.`second`=?) ORDER BY `event_trigger`.`priority` DESC, `event_element`.`parent_id`, `event_element`.`order`',
            [
                1,
                'arthur',
                'dent',
                (int) $date->format('w'),
                (int) $date->format('j'),
                (int) $date->format('n'),
                (int) $date->format('Y'),
                (int) $date->format('H'),
                (int) $date->format('i'),
                (int) $date->format('s'),
            ],
        )
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->assertEquals([], $this->eventRepository->getTimeControlled('arthur', 'dent', $date));
    }
}
