<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\DateTimeService;
use MDO\Dto\Query\Where;
use MDO\Enum\OrderDirection;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;

class WeatherRepositoryTest extends Unit
{
    use RepositoryTrait;

    private WeatherRepository $weatherRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->loadRepository('weather');
        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->weatherRepository = new WeatherRepository(
            $this->repositoryWrapper->reveal(),
            $this->dateTimeService->reveal(),
            'weather',
        );
    }

    public function testGetByDate(): void
    {
        $date = new DateTimeImmutable();
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`location_id`=? AND `date`=?', [42, $date->format('Y-m-d H:i:s')]))
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Weather::class);
        $weather = $this->weatherRepository->getByDate((new Location($this->modelWrapper->reveal()))->setId(42), $date);

        $model->setDate($date);
        $weather->setDate($date);

        $this->assertEquals($model, $weather);
    }

    public function testGetByNearestDate(): void
    {
        $date = new DateTimeImmutable();
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`location_id`=? AND `date`<=?', [42, $date->format('Y-m-d H:i:s')]))
            ->setOrder('`date`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Weather::class);
        $weather = $this->weatherRepository->getByNearestDate((new Location($this->modelWrapper->reveal()))->setId(42), $date);

        $model->setDate($date);
        $weather->setDate($date);

        $this->assertEquals($model, $weather);
    }

    public function testGetByNearestDateNull(): void
    {
        $date = new DateTime();
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`location_id`=? AND `date`<=?', [42, $date->format('Y-m-d H:i:s')]))
            ->setOrder('`date`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $this->dateTimeService->get('now', new DateTimeZone('Europe/Berlin'))
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;
        $model = $this->loadModel($selectQuery, Weather::class);
        $weather = $this->weatherRepository->getByNearestDate(
            (new Location($this->modelWrapper->reveal()))->setId(42)->setTimezone('Europe/Berlin'),
        );

        $model->setDate($date);
        $weather->setDate($date);

        $this->assertEquals($model, $weather);
    }

    public function testDeleteBetweenDate(): void
    {
        $from = new DateTimeImmutable('-1 hour');
        $to = new DateTimeImmutable('+1 hour');
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`location_id`=?', [42]))
            ->addWhere(new Where('`date`>?', [$from->format('Y-m-d H:i:s')]))
            ->addWhere(new Where('`date`<?', [$to->format('Y-m-d H:i:s')]))
        ;
        $this->loadDeleteQuery($deleteQuery);

        $this->assertTrue($this->weatherRepository->deleteBetweenDates(
            (new Location($this->modelWrapper->reveal()))->setId(42),
            $from,
            $to,
        ));
    }
}
