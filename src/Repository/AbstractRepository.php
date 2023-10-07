<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use Generator;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\RepositoryService;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;

abstract class AbstractRepository
{
    public function __construct(protected readonly RepositoryService $repositoryService)
    {
    }

    protected function startTransaction(): void
    {
        $this->repositoryService->getClient()->startTransaction();
    }

    protected function commit(): void
    {
        $this->repositoryService->getClient()->commit();
    }

    protected function rollback(): void
    {
        $this->repositoryService->getClient()->rollback();
    }

    public function isTransaction(): bool
    {
        return $this->repositoryService->getClient()->isTransaction();
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<T>
     */
    protected function getModels(SelectQuery $selectQuery, string $modelClassName): Generator
    {
        $response = $this->repositoryService->getClient()->execute($selectQuery);

        foreach ($response->iterateRecords() as $record) {
            $model = new $modelClassName($this->repositoryService->getModelService());
            $this->repositoryService->getModelManager()->loadFromRecord($record, $model);

            yield $model;
        }
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @return T
     */
    protected function getModel(Record $record, string $modelClassName): AbstractModel
    {
        $model = new $modelClassName($this->repositoryService->getModelService());
        $this->repositoryService->getModelManager()->loadFromRecord($record, $model);

        return $model;
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>               $modelClassName
     * @param array<string, OrderDirection> $orderBy
     *
     * @throws ClientException
     * @throws SelectError
     *
     * @return T
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $modelClassName,
        array $orderBy = [],
    ): ModelInterface {
        $model = new $modelClassName($this->repositoryService->getModelService());
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setLimit(1)
            ->setOrders($orderBy)
        ;

        $result = $this->repositoryService->getClient()->execute($selectQuery);
        $record = $result->iterateRecords()->current();

        if (!$record instanceof Record) {
            $exception = new SelectError('No results!');
            $exception->setTable($this->repositoryService->getTableManager()->getTable($model->getTableName()));

            throw $exception;
        }

        return $this->getModel($record, $model);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>               $modelClassName
     * @param array<string, OrderDirection> $orderBy
     *
     * @throws ClientException
     * @throws SelectError
     *
     * @return Generator<T>
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $modelClassName,
        int $limit = null,
        int $offset = null,
        array $orderBy = [],
    ): Generator {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setLimit($limit, $offset)
            ->setOrders($orderBy)
        ;

        return $this->getModels($selectQuery, $modelClassName);
    }

    /**
     * @param class-string<ModelInterface> $modelClassName
     */
    protected function getAggregate(
        string $function,
        string $modelClassName,
        string $where = '',
        array $parameters = [],
    ): ?array {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setSelects(['aggr' => $function])
        ;
        $result = $this->repositoryService->getClient()->execute($selectQuery);

        return $result->iterateRecords()->current()?->get('aggr')->value();
    }

    /**
     * @throws ClientException
     */
    protected function getSelectQuery(string $tableName, string $alias = null): SelectQuery
    {
        return new SelectQuery($this->repositoryService->getTableManager()->getTable($tableName), $alias);
    }

    protected function getRegexString(string $search): string
    {
        return $this->repositoryService->getSelectService()->getUnescapedRegexString($search);
    }
}
