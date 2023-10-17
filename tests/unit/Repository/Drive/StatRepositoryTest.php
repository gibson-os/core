<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Drive;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Repository\Drive\StatRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Result;
use MDO\Dto\Value;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;

class StatRepositoryTest extends Unit
{
    use RepositoryTrait;

    private StatRepository $statRepository;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->loadRepository('system_drive_stat');

        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->statRepository = new StatRepository($this->repositoryWrapper->reveal(), $this->dateTimeService->reveal());
    }

    public function testGetTimeRange(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('1', []))
            ->setSelects(['min' => 'MIN(`added`)', 'max' => 'MAX(`added`)'])
        ;
        $this->loadAggregation(
            $selectQuery,
            new Record(['min' => new Value('arthur'), 'max' => new Value('dent')]),
        );
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
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('1', []))
            ->setSelects(['min' => 'MIN(`added`)', 'max' => 'MAX(`added`)'])
        ;
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([new Record(['min' => new Value(null), 'max' => new Value(null)])])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result)
        ;
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
            ->willReturn($this->modelWrapper->reveal())
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->assertEquals(
            ['min' => 0, 'max' => 0],
            $this->statRepository->getTimeRange(),
        );
    }
}
