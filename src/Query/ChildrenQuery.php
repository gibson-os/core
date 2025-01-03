<?php
declare(strict_types=1);

namespace GibsonOS\Core\Query;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Wrapper\ModelWrapper;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Query\With;
use MDO\Dto\Select;
use MDO\Dto\Table;
use MDO\Enum\JoinType;
use MDO\Exception\ClientException;
use MDO\Manager\TableManager;
use MDO\Query\DeleteQuery;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;
use ReflectionException;
use ReflectionProperty;

class ChildrenQuery
{
    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        private readonly TableManager $tableManager,
        private readonly ModelWrapper $modelWrapper,
        private readonly SelectService $selectService,
    ) {
    }

    /**
     * @throws ClientException
     * @throws ReflectionException
     */
    public function getSelectQuery(AbstractModel $model, string $alias, ChildrenMapping $child): SelectQuery
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($model);
        $reflectionProperty = $reflectionClass->getProperty($child->getPropertyName());
        $constraintAttribute = $this->reflectionManager->getAttribute($reflectionProperty, Constraint::class);

        if ($constraintAttribute === null) {
            throw new ReflectionException(sprintf(
                'Property "%s::%s" has not constraint attribute',
                $model::class,
                $child->getPropertyName(),
            ));
        }

        $modelClassName = $this->getModelClassName($reflectionProperty, $constraintAttribute);

        /** @var AbstractModel $childModel */
        $childModel = new $modelClassName($this->modelWrapper);
        $ownReflectionProperty = $reflectionClass->getProperty($this->getOwnProperty($reflectionProperty, $constraintAttribute));
        $selectQuery = (new SelectQuery($this->tableManager->getTable($childModel->getTableName()), $alias))
            ->setWheres($child->getWheres())
            ->addWhere(new Where(
                sprintf('`%s`.`%s`=?', $alias, $this->getChildColumn($reflectionProperty, $constraintAttribute)),
                [$this->reflectionManager->getProperty($ownReflectionProperty, $model)],
            ))
        ;
        $where = $constraintAttribute->getWhere();

        if ($where !== null) {
            $selectQuery->addWhere(new Where($where, $constraintAttribute->getWhereParameters()));
        }

        return $this->extend($selectQuery, $model::class, $child->getChildren());
    }

    public function getDeleteQuery(AbstractModel $model, string $alias, ChildrenMapping $child): DeleteQuery
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($model);
        $reflectionProperty = $reflectionClass->getProperty($child->getPropertyName());
        $constraintAttribute = $this->reflectionManager->getAttribute($reflectionProperty, Constraint::class);

        if ($constraintAttribute === null) {
            throw new ReflectionException(sprintf(
                'Property "%s::%s" has not constraint attribute',
                $model::class,
                $child->getPropertyName(),
            ));
        }

        $modelClassName = $this->getModelClassName($reflectionProperty, $constraintAttribute);

        /** @var AbstractModel $childModel */
        $childModel = new $modelClassName($this->modelWrapper);
        $ownReflectionProperty = $reflectionClass->getProperty($this->getOwnProperty($reflectionProperty, $constraintAttribute));
        $deleteQuery = (new DeleteQuery($this->tableManager->getTable($childModel->getTableName()), $alias))
            ->setWheres($child->getWheres())
            ->addWhere(new Where(
                sprintf('`%s`.`%s`=?', $alias, $this->getChildColumn($reflectionProperty, $constraintAttribute)),
                [$this->reflectionManager->getProperty($ownReflectionProperty, $model)],
            ))
        ;
        $where = $constraintAttribute->getWhere();

        if ($where !== null) {
            $deleteQuery->addWhere(new Where($where, $constraintAttribute->getWhereParameters()));
        }

        return $deleteQuery;
    }

    /**
     * @param class-string      $modelClassName
     * @param ChildrenMapping[] $children
     *
     * @throws ReflectionException
     * @throws ClientException
     */
    public function extend(SelectQuery $selectQuery, string $modelClassName, array $children, ?string $alias = null, $addWith = true): SelectQuery
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($modelClassName);
        $selects = [];

        if (count($children) === 0) {
            return $selectQuery;
        }

        $newSelectQuery = $selectQuery;

        if ($addWith) {
            $withTableName = sprintf('with_%s', $selectQuery->getTable()->getTableName());
            $withQuery = new SelectQuery($selectQuery->getTable(), $selectQuery->getAlias());
            $withQuery->setLimit($selectQuery->getRowCount(), $selectQuery->getOffset());

            $newSelectQuery = new SelectQuery(new Table($withTableName, $selectQuery->getTable()->getFields()), $selectQuery->getAlias());
            $newSelectQuery->setSelects($selectQuery->getSelects());
            $newSelectQuery->setWiths($selectQuery->getWiths());
            $newSelectQuery->setWith(new With($withTableName, $withQuery));
            $newSelectQuery->setJoins($selectQuery->getJoins());
            $newSelectQuery->setWheres($selectQuery->getWheres());
            $newSelectQuery->setOrders($selectQuery->getOrders());
            $newSelectQuery->setGroupBy($selectQuery->getGroupBy(), $selectQuery->getHaving());
        }

        foreach ($children as $child) {
            $reflectionProperty = $reflectionClass->getProperty($child->getPropertyName());
            $constraintAttribute = $this->reflectionManager->getAttribute($reflectionProperty, Constraint::class);

            if ($constraintAttribute === null) {
                throw new ReflectionException(sprintf(
                    'Property "%s::%s" has not constraint attribute',
                    $modelClassName,
                    $child->getPropertyName(),
                ));
            }

            $parentModelClassName = $this->getModelClassName($reflectionProperty, $constraintAttribute);
            /** @var AbstractModel $parentModel */
            $parentModel = new $parentModelClassName($this->modelWrapper);
            $table = $this->tableManager->getTable($parentModel->getTableName());
            $wheres = [];

            foreach ($child->getWheres() as $where) {
                $newSelectQuery->addParameters($where->getParameters());
                $wheres[] = $where->getCondition();
            }

            $childAlias = $child->getAlias();
            $newSelectQuery
                ->addJoin(new Join(
                    $table,
                    $childAlias,
                    sprintf(
                        '`%s`.`%s`=`%s`.`%s`%s',
                        $alias ?? $newSelectQuery->getAlias() ?? $newSelectQuery->getTable()->getTableName(),
                        $this->getOwnColumn($reflectionProperty, $constraintAttribute),
                        $childAlias,
                        $this->getChildColumn($reflectionProperty, $constraintAttribute),
                        count($wheres) === 0 ? '' : ' AND (' . implode(') AND (', $wheres) . ')',
                    ),
                    JoinType::LEFT,
                ))
            ;
            $where = $constraintAttribute->getWhere();

            if ($where !== null) {
                $newSelectQuery->addWhere(new Where($where, $constraintAttribute->getWhereParameters()));
            }

            foreach ($constraintAttribute->getOrderBy() as $field => $orderDirection) {
                $newSelectQuery->setOrder(sprintf('`%s`.`%s`', $childAlias, $field), $orderDirection);
            }

            $newSelectQuery = $this->extend($newSelectQuery, $parentModelClassName, $child->getChildren(), $childAlias, false);
            $selects[] = new Select($table, $childAlias, $child->getPrefix());
        }

        $newSelectQuery->setSelects(array_merge(
            $newSelectQuery->getSelects(),
            $this->selectService->getSelects($selects),
        ));

        return $newSelectQuery;
    }

    private function getOwnColumn(
        ReflectionProperty $reflectionProperty,
        Constraint $constraintAttribute,
    ): string {
        $typeName = $this->reflectionManager->getTypeName($reflectionProperty);

        if ($typeName === 'array') {
            return $constraintAttribute->getOwnColumn() ?? 'id';
        }

        return $constraintAttribute->getOwnColumn()
            ?? (mb_strtolower(preg_replace('/([A-Z])/', '_$1', $reflectionProperty->getName())) . '_id')
        ;
    }

    private function transformFieldName(string $fieldName): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName))));
    }

    private function getOwnProperty(
        ReflectionProperty $reflectionProperty,
        Constraint $constraintAttribute,
    ): string {
        $typeName = $this->reflectionManager->getTypeName($reflectionProperty);

        if ($typeName === 'array') {
            return $this->transformFieldName($constraintAttribute->getOwnColumn() ?? 'id');
        }

        return $this->transformFieldName($constraintAttribute->getOwnColumn() ?? ($reflectionProperty->getName() . 'Id'));
    }

    private function getChildColumn(
        ReflectionProperty $reflectionProperty,
        Constraint $constraintAttribute,
    ): string {
        $typeName = $this->reflectionManager->getTypeName($reflectionProperty);
        $parentColumn = mb_strtolower(preg_replace('/([A-Z])/', '_$1', $constraintAttribute->getParentColumn()));

        if ($typeName === 'array') {
            return $parentColumn . '_id';
        }

        return $parentColumn;
    }

    /**
     * @throws ReflectionException
     *
     * @return class-string<AbstractModel>
     */
    private function getModelClassName(
        ReflectionProperty $reflectionProperty,
        Constraint $constraintAttribute,
    ): string {
        $parentModelClassName = $this->reflectionManager->getTypeName($reflectionProperty);

        if ($parentModelClassName === 'array') {
            $parentModelClassName = $constraintAttribute->getParentModelClassName();
        }

        if ($parentModelClassName === null || !is_subclass_of($parentModelClassName, AbstractModel::class)) {
            throw new ReflectionException(sprintf(
                'Property "parentModelClassName" of constraint attribute for property "%s::%s" is not set!',
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName(),
            ));
        }

        return $parentModelClassName;
    }
}
