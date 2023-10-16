<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use GibsonOS\Core\Mapper\Model\ChildrenMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Query\ChildrenQuery;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use MDO\Dto\Record;
use MDO\Dto\Result;
use MDO\Dto\Table;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;

trait RepositoryTrait
{
    use ModelManagerTrait;

    private Table $table;

    private ObjectProphecy|RepositoryWrapper $repositoryWrapper;

    private function loadRepository(string $tableName, array $fields): void
    {
        $this->loadModelManager();

        $this->repositoryWrapper = $this->prophesize(RepositoryWrapper::class);
        $this->table = new Table($tableName, $fields);
    }

    private function loadModel(SelectQuery $selectQuery, string $modelClassName, string $prefix = null): AbstractModel
    {
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledTimes(2)
            ->willReturn($this->modelWrapper)
        ;
        $this->tableManager->getTable($this->table->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->tableManager)
        ;
        $childrenQuery = $this->prophesize(ChildrenQuery::class);
        $childrenQuery->extend($selectQuery, $modelClassName, []);
        $this->repositoryWrapper->getChildrenQuery()
            ->shouldBeCalledOnce()
            ->willReturn($childrenQuery->reveal())
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $this->repositoryWrapper->getClient()
            ->shouldBeCalledOnce()
            ->willReturn($this->client->reveal())
        ;
        $this->repositoryWrapper->getModelManager()
            ->shouldBeCalledOnce()
            ->willReturn($this->modelManager->reveal())
        ;
        $childrenMapper = $this->prophesize(ChildrenMapper::class);
        $this->repositoryWrapper->getChildrenMapper()
            ->shouldBeCalledOnce()
            ->willReturn($childrenMapper->reveal())
        ;
        $model = new $modelClassName($this->modelWrapper->reveal());

        if ($prefix === null) {
            $this->modelManager->loadFromRecord($record, $model)
                ->shouldBeCalledOnce()
            ;
        } else {
            $this->modelManager->loadFromRecord($record, $model, $prefix)
                ->shouldBeCalledOnce()
            ;
        }

        return $model;
    }
}
