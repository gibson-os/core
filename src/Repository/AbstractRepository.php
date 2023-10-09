<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
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
use MDO\Query\SelectQuery;
use ReflectionClass;
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
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return T[]
     */
    protected function getModels(
        SelectQuery $selectQuery,
        string $modelClassName,
        string $prefix = '',
        array $children = [],
    ): array {
        $response = $this->repositoryWrapper->getClient()->execute($selectQuery);
        $modelService = $this->repositoryWrapper->getModelService();
        $reflectionManager = $this->repositoryWrapper->getReflectionManager();
        $modelReflection = $reflectionManager->getReflectionClass($modelClassName);
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

            $this->getChildModels($record, $models[$primaryKey], $modelReflection, $children);
        }

        return array_values($models);
    }

    /**
     * @param ChildrenMapping[] $children
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ClientException
     */
    private function getChildModels(
        Record $record,
        AbstractModel $model,
        ReflectionClass $modelReflection,
        array $children,
    ): void {
        $reflectionManager = $this->repositoryWrapper->getReflectionManager();
        $modelService = $this->repositoryWrapper->getModelService();

        foreach ($children as $child) {
            $propertyName = $child->getPropertyName();
            $propertyReflection = $modelReflection->getProperty($propertyName);
            $isArray = false;
            $uppercasePropertyName = ucfirst($propertyName);

            try {
                $childModelClassName = $reflectionManager->getNonBuiltinTypeName($propertyReflection);
                $setter = sprintf('set%s', $uppercasePropertyName);
            } catch (ReflectionException) {
                $childModelClassName = $reflectionManager
                    ->getAttribute($propertyReflection, Constraint::class)
                    ->getParentModelClassName()
                ;
                $isArray = true;
                $setter = sprintf('add%s', $uppercasePropertyName);
            }

            // @todo gucken ob das child model schon existiert
            /** @var AbstractModel $childModel */
            $childModel = new $childModelClassName($modelService);
            $primaryKeys = $this->repositoryWrapper->getTableManager()->getTable($childModel->getTableName());

            if ($isArray) {
                /** @var AbstractModel $existingChild */
                foreach ($model->{'get' . $uppercasePropertyName}() as $existingChild) {

                }
            }

            $this->repositoryWrapper->getModelManager()->loadFromRecord($record, $childModel, $child->getPrefix());

            if ($isArray) {
                $childModel = [$childModel];
            }

            $this->getChildModels(
                $record,
                $childModel,
                $reflectionManager->getReflectionClass($childModelClassName),
                $child->getChildren(),
            );
            $model->$setter($childModel);
        }
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     *
     * @return AbstractModel<T>
     */
    protected function getModel(SelectQuery $selectQuery, string $modelClassName, array $children = []): AbstractModel
    {
        $result = $this->repositoryWrapper->getClient()->execute($selectQuery);
        $record = $result?->iterateRecords()->current();

        if (!$record instanceof Record) {
            $exception = new SelectError('No results!');
            $exception->setTable($this->repositoryWrapper->getTableManager()->getTable($selectQuery->getTable()->getTableName()));

            throw $exception;
        }

        $model = new $modelClassName($this->repositoryWrapper->getModelService());
        $this->repositoryWrapper->getModelManager()->loadFromRecord($record, $model);

        $this->getChildModels(
            $record,
            $model,
            $this->repositoryWrapper->getReflectionManager()->getReflectionClass($modelClassName),
            $children,
        );

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
     * @return AbstractModel<T>
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $modelClassName,
        array $orderBy = [],
        array $children = [],
    ): ModelInterface {
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
     *
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return T[]
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $modelClassName,
        int $limit = null,
        int $offset = null,
        array $orderBy = [],
    ): array {
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
     *
     * @throws ClientException
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
