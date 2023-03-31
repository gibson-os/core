<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Drive;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Repository\Drive\StatRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;

class StatRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private StatRepository $statRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->loadModelManager();
        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`drive_stat`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['drive_id', 'bigint(42)', 'NO', '', null, ''],
                ['disk', 'bigint(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->statRepository = new StatRepository(
            $this->dateTimeService->reveal(),
            'drive_stat',
        );
    }

    public function testGetTimeRange(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT MIN(`added`) AS `min`, MAX(`added`) AS `max` FROM `marvin`.`drive_stat`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(4)
            ->willReturn(
                ['drive_id', 'bigint(42)', 'NO', '', null, ''],
                ['disk', 'bigint(42)', 'NO', '', null, ''],
                null,
                ['min' => 'arthur', 'max' => 'dent'],
            )
        ;
        $min = new DateTime('-1 hour');
        $max = new DateTime('+1 hour');
        $this->dateTimeService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn($min)
        ;
        $this->dateTimeService->get('dent')
            ->shouldBeCalledOnce()
            ->willReturn($max)
        ;

        $this->assertEquals(
            ['min' => $min->getTimestamp(), 'max' => $max->getTimestamp()],
            $this->statRepository->getTimeRange(),
        );
    }

    public function testGetTimeRangeEmpty(): void
    {
        $this->mysqlDatabase->execute(
            'SELECT MIN(`added`) AS `min`, MAX(`added`) AS `max` FROM `marvin`.`drive_stat`',
            [],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(4)
            ->willReturn(
                ['drive_id', 'bigint(42)', 'NO', '', null, ''],
                ['disk', 'bigint(42)', 'NO', '', null, ''],
                null,
                null,
            )
        ;

        $this->assertEquals(
            ['min' => 0, 'max' => 0],
            $this->statRepository->getTimeRange(),
        );
    }
}
