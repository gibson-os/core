<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Weather;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class LocationRepositoryTest extends Unit
{
    use RepositoryTrait;

    private LocationRepository $locationRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private LoggerInterface|ObjectProphecy $logger;

    protected function _before()
    {
        $this->loadRepository('weather_location');

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->locationRepository = new LocationRepository(
            $this->repositoryWrapper->reveal(),
            $this->dateTimeService->reveal(),
            $this->logger->reveal(),
        );
    }

    public function testGetById(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Location::class),
            $this->locationRepository->getById(42),
        );
    }

    public function testFindByName(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name` LIKE ?', ['arthur%']))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Location::class),
            $this->locationRepository->findByName('arthur', false)[0],
        );
    }

    public function testFindByNameOnlyActive(): void
    {
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where('`name` LIKE ? AND `active`=?', ['arthur%', 1]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Location::class),
            $this->locationRepository->findByName('arthur', true)[0],
        );
    }

    public function testGetToUpdate(): void
    {
        $date = new DateTime();
        $selectQuery = (new SelectQuery($this->table, 't'))
            ->addWhere(new Where(
                '`active`=? AND (`last_run` IS NULL OR FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`) <= ?)',
                [1, $date->format('Y-m-d H:i:s')],
            ))
        ;

        $this->dateTimeService->get()
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Location::class),
            $this->locationRepository->getToUpdate()[0],
        );
    }
}
