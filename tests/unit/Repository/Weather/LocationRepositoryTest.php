<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Weather;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class LocationRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private LocationRepository $locationRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private LoggerInterface|ObjectProphecy $logger;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`weather_location`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['name', 'varchar(42)', 'NO', '', null, ''],
                ['active', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->locationRepository = new LocationRepository(
            $this->dateTimeService->reveal(),
            $this->logger->reveal(),
            $this->modelManager->reveal(),
            'weather_location',
        );
    }

    public function testGetById(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `weather_location`.`name`, `weather_location`.`active` FROM `marvin`.`weather_location` WHERE `id`=? LIMIT 1',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
                'active' => '1',
            ]])
        ;

        $location = $this->locationRepository->getById(42);

        $this->assertEquals('marvin', $location->getName());
        $this->assertTrue($location->isActive());
    }

    public function testFindByName(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `weather_location`.`name`, `weather_location`.`active` FROM `marvin`.`weather_location` WHERE `user` LIKE ?',
            ['arthur%'],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
                'active' => '1',
            ]])
        ;

        $location = $this->locationRepository->findByName('arthur', false)[0];

        $this->assertEquals('marvin', $location->getName());
        $this->assertTrue($location->isActive());
    }

    public function testFindByNameOnlyActive(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT `weather_location`.`name`, `weather_location`.`active` FROM `marvin`.`weather_location` WHERE `user` LIKE ? AND `active`=?',
            ['arthur%', 1],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
                'active' => '1',
            ]])
        ;

        $location = $this->locationRepository->findByName('arthur', true)[0];

        $this->assertEquals('marvin', $location->getName());
        $this->assertTrue($location->isActive());
    }

    public function testGetToUdate(): void
    {
        $date = new DateTime();
        $this->mysqlDatabase->execute(
            'SELECT `weather_location`.`name`, `weather_location`.`active` FROM `marvin`.`weather_location` WHERE `active`=1 AND (`last_run` IS NULL OR FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`) <= ?)',
            [$date->format('Y-m-d H:i:s')],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
            ->shouldBeCalledOnce()
            ->willReturn([[
                'name' => 'marvin',
                'active' => '1',
            ]])
        ;
        $this->dateTimeService->get()
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;

        $location = $this->locationRepository->getToUpdate()[0];

        $this->assertEquals('marvin', $location->getName());
        $this->assertTrue($location->isActive());
    }
}
