<?php
declare(strict_types=1);

namespace GibsonOS\Core\Query;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Exception\QueryException;
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
    public function extend(SelectQuery $selectQuery, string $modelClassName, array $children): SelectQuery
    {
        if ($children === []) {
            return $selectQuery;
        }

        $selectQuery = $this->extendQuery($selectQuery, $modelClassName, $children);

        return $this->extendWith($selectQuery);
    }

    private function extendWith(SelectQuery $selectQuery): SelectQuery
    {
        $table = $selectQuery->getTable();
        $tableName = $table->getTableName();
        $alias = $selectQuery->getAlias();
        $withTableName = sprintf('with_%s', $tableName);

        $groupBy = [];
        $ons = [];
        $selects = $selectQuery->getSelects();
        $selectQuery->setSelects([]);

        foreach ($table->getPrimaryFields() as $field) {
            $fieldName = $field->getName();
            $fieldString = ($alias === null ? '' : '`' . $alias . '`.') . '`' . $fieldName . '`';
            $selectQuery->setSelect($fieldString, $fieldName);
            $groupBy[] = $fieldString;
            $ons[] = sprintf('`%s`.`%s`=%s', $withTableName, $fieldName, $fieldString);
        }

        $selectQuery->setGroupBy($groupBy);
        $newSelectQuery = (new SelectQuery($table, $alias))
            ->setSelects($selects)
            ->setJoins($selectQuery->getJoins())
            ->addJoin(new Join(new Table($withTableName, $table->getFields()), $withTableName, implode(' AND ', $ons)))
            ->setWiths($selectQuery->getWiths())
            ->setWith(new With($withTableName, $selectQuery))
            ->setWheres($selectQuery->getWheres())
            ->setOrders($selectQuery->getOrders())
            ->addParameters(array_merge($selectQuery->getParameters(), $selectQuery->getParameters()))
        ;

        $selectQuery->setWiths([]);

        return $newSelectQuery;
    }

    /**
     * @param class-string      $modelClassName
     * @param ChildrenMapping[] $children
     *
     * @throws ReflectionException
     * @throws ClientException
     */
    private function extendQuery(SelectQuery $selectQuery, string $modelClassName, array $children, ?string $alias = null): SelectQuery
    {
        if ($children === []) {
            return $selectQuery;
        }

        $reflectionClass = $this->reflectionManager->getReflectionClass($modelClassName);
        $selects = [];

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
                $selectQuery->addParameters($where->getParameters());
                $wheres[] = $where->getCondition();
            }

            $childAlias = $child->getAlias();
            $selectQuery
                ->addJoin(new Join(
                    $table,
                    $childAlias,
                    sprintf(
                        '`%s`.`%s`=`%s`.`%s`%s',
                        $alias ?? $selectQuery->getAlias() ?? $selectQuery->getTable()->getTableName(),
                        $this->getOwnColumn($reflectionProperty, $constraintAttribute),
                        $childAlias,
                        $this->getChildColumn($reflectionProperty, $constraintAttribute),
                        $wheres === [] ? '' : ' AND (' . implode(') AND (', $wheres) . ')',
                    ),
                    JoinType::LEFT,
                ))
            ;
            $where = $constraintAttribute->getWhere();

            if ($where !== null) {
                $selectQuery->addWhere(new Where($where, $constraintAttribute->getWhereParameters()));
            }

            foreach ($constraintAttribute->getOrderBy() as $field => $orderDirection) {
                $selectQuery->setOrder(sprintf('`%s`.%s', $childAlias, $field), $orderDirection);
            }

            $selectQuery = $this->extendQuery($selectQuery, $parentModelClassName, $child->getChildren(), $childAlias);
            $selects[] = new Select($table, $childAlias, $child->getPrefix());
        }

        $selectQuery->setSelects(array_merge(
            $selectQuery->getSelects(),
            $this->selectService->getSelects($selects),
        ));

        return $selectQuery;
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
            ?? (mb_strtolower(
                preg_replace(
                    '/([A-Z])/',
                    '_$1',
                    $reflectionProperty->getName(),
                ) ?: throw new QueryException('Property name is not set!'),
            ) . '_id')
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
        $parentColumn = mb_strtolower(
            preg_replace(
                '/([A-Z])/',
                '_$1',
                $constraintAttribute->getParentColumn(),
            ) ?? throw new QueryException('Parent column is not set!'),
        );

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
