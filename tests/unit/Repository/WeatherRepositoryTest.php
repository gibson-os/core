<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class WeatherRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private WeatherRepository $weatherRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`weather`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(2)
            ->willReturn(
                ['temperature', 'float(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->weatherRepository = new WeatherRepository($this->dateTimeService->reveal(), 'weather');
    }

    public function testGetByDate(): void
    {
        $date = new DateTimeImmutable();

        $this->mysqlDatabase->execute(
            'SELECT `weather`.`temperature` FROM `marvin`.`weather` WHERE `location_id`=? AND date=? LIMIT 1',
            [42, $date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'temperature' => '4.2',
            ]])
        ;

        $weather = $this->weatherRepository->getByDate(
            (new Location())->setId(42),
            $date,
        );

        $this->assertEquals(4.2, $weather->getTemperature());
    }

    public function testGetByNearestDate(): void
    {
        $date = new DateTimeImmutable();

        $this->mysqlDatabase->execute(
            'SELECT `weather`.`temperature` FROM `marvin`.`weather` WHERE `location_id`=? AND `date`<=? ORDER BY `date` DESC LIMIT 1',
            [42, $date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'temperature' => '4.2',
            ]])
        ;

        $weather = $this->weatherRepository->getByNearestDate(
            (new Location())->setId(42),
            $date,
        );

        $this->assertEquals(4.2, $weather->getTemperature());
    }

    public function testGetByNearestDateNull(): void
    {
        $date = new DateTime();

        $this->mysqlDatabase->execute(
            'SELECT `weather`.`temperature` FROM `marvin`.`weather` WHERE `location_id`=? AND `date`<=? ORDER BY `date` DESC LIMIT 1',
            [42, $date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'temperature' => '4.2',
            ]])
        ;
        $this->dateTimeService->get('now', Argument::exact(new DateTimeZone('Europe/Berlin')))
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;

        $weather = $this->weatherRepository->getByNearestDate((new Location())->setId(42)->setTimezone('Europe/Berlin'));

        $this->assertEquals(4.2, $weather->getTemperature());
    }

    public function testDeleteBetweenDate(): void
    {
        $from = new DateTimeImmutable('-1 hour');
        $to = new DateTimeImmutable('+1 hour');

        $this->mysqlDatabase->execute(
            'DELETE `weather` FROM `marvin`.`weather` WHERE `location_id`=? AND `date` > ? AND `date` < ? ',
            [42, $from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->assertTrue($this->weatherRepository->deleteBetweenDates(
            (new Location())->setId(42),
            $from,
            $to,
        ));
    }
}
