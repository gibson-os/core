<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Field;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\SelectQuery;
use ReflectionException;

abstract class AbstractRepository
{
    public function __construct(private readonly RepositoryWrapper $repositoryWrapper)
    {
    }

    public function getRepositoryWrapper(): RepositoryWrapper
    {
        return $this->repositoryWrapper;
    }

    public function startTransaction(): void
    {
        $this->repositoryWrapper->getClient()->startTransaction();
    }

    public function commit(): void
    {
        $this->repositoryWrapper->getClient()->commit();
    }

    public function rollback(): void
    {
        $this->repositoryWrapper->getClient()->rollback();
    }

    public function isTransaction(): bool
    {
        return $this->repositoryWrapper->getClient()->isTransaction();
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>   $modelClassName
     * @param ChildrenMapping[] $children
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return T[]
     */
    protected function getModels(
        SelectQuery $selectQuery,
        string $modelClassName,
        string $prefix = '',
        array $children = [],
    ): array {
        $this->repositoryWrapper->getChildrenQuery()->extend($selectQuery, $modelClassName, $children);
        $response = $this->repositoryWrapper->getClient()->execute($selectQuery);
        $modelService = $this->repositoryWrapper->getModelService();
        $models = [];
        $primaryKey = implode('-', array_map(
            static fn (Field $primaryField): string => $primaryField->getName(),
            $selectQuery->getTable()->getPrimaryFields(),
        ));

        foreach ($response?->iterateRecords() ?? [] as $record) {
            if (!isset($models[$primaryKey])) {
                $model = new $modelClassName($modelService);
                $this->repositoryWrapper->getModelManager()->loadFromRecord($record, $model, $prefix);
                $models[$primaryKey] = $model;
            }

            $this->repositoryWrapper->getChildrenMapper()->getChildrenModels($record, $models[$primaryKey], $children);
        }

        return array_values($models);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>   $modelClassName
     * @param ChildrenMapping[] $children
     *
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     * @throws ClientException
     * @throws JsonException
     *
     * @return T
     */
    protected function getModel(SelectQuery $selectQuery, string $modelClassName, array $children = []): AbstractModel
    {
        $this->repositoryWrapper->getChildrenQuery()->extend($selectQuery, $modelClassName, $children);
        $result = $this->repositoryWrapper->getClient()->execute($selectQuery);
        $record = $result?->iterateRecords()->current();

        if (!$record instanceof Record) {
            $exception = new SelectError('No results!');
            $exception->setTable($this->repositoryWrapper->getTableManager()->getTable($selectQuery->getTable()->getTableName()));

            throw $exception;
        }

        $model = new $modelClassName($this->repositoryWrapper->getModelService());
        $this->repositoryWrapper->getModelManager()->loadFromRecord($record, $model);

        $this->repositoryWrapper->getChildrenMapper()->getChildrenModels(
            $record,
            $model,
            $children,
        );

        return $model;
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>               $modelClassName
     * @param array<string, OrderDirection> $orderBy
     * @param ChildrenMapping[]             $children
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     * @throws ClientException
     *
     * @return T
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $modelClassName,
        array $orderBy = [],
        array $children = [],
    ): AbstractModel {
        $model = new $modelClassName($this->repositoryWrapper->getModelService());
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setLimit(1)
            ->setOrders($orderBy)
        ;

        return $this->getModel($selectQuery, $modelClassName, $children);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T>               $modelClassName
     * @param array<string, OrderDirection> $orderBy
     * @param ChildrenMapping[]             $children
     *
     * @throws ReflectionException
     * @throws ClientException
     * @throws RecordException
     * @throws JsonException
     *
     * @return T[]
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $modelClassName,
        int $limit = 0,
        int $offset = 0,
        array $orderBy = [],
        string $prefix = '',
        array $children = [],
    ): array {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setLimit($limit, $offset)
            ->setOrders($orderBy)
        ;

        return $this->getModels($selectQuery, $modelClassName, $prefix, $children);
    }

    /**
     * @param class-string<ModelInterface> $modelClassName
     *
     * @throws ClientException
     * @throws SelectError
     */
    protected function getAggregations(
        array $functions,
        string $modelClassName,
        string $where = '',
        array $parameters = [],
    ): Record {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $selectQuery = $this->getSelectQuery($model->getTableName())
            ->addWhere(new Where($where, $parameters))
            ->setSelects($functions)
        ;
        $result = $this->repositoryWrapper->getClient()->execute($selectQuery);

        if ($result === null) {
            throw new SelectError();
        }

        return $result->iterateRecords()->current();
    }

    /**
     * @throws ClientException
     */
    protected function getSelectQuery(string $tableName, string $alias = null): SelectQuery
    {
        return new SelectQuery($this->repositoryWrapper->getTableManager()->getTable($tableName), $alias);
    }

    protected function getRegexString(string $search): string
    {
        return $this->repositoryWrapper->getSelectService()->getUnescapedRegexString($search);
    }
}
