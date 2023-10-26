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
use MDO\Dto\Select;
use MDO\Enum\JoinType;
use MDO\Exception\ClientException;
use MDO\Manager\TableManager;
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
    public function getQuery(AbstractModel $model, string $alias, ChildrenMapping $child): SelectQuery
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

    /**
     * @param class-string      $modelClassName
     * @param ChildrenMapping[] $children
     *
     * @throws ReflectionException
     * @throws ClientException
     */
    public function extend(SelectQuery $selectQuery, string $modelClassName, array $children): SelectQuery
    {
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
            $selectQuery
                ->addJoin(new Join(
                    $table,
                    $child->getAlias(),
                    sprintf(
                        '`%s`.`%s`=`%s`.`%s`',
                        $selectQuery->getAlias() ?? $selectQuery->getTable()->getTableName(),
                        $this->getOwnColumn($reflectionProperty, $constraintAttribute),
                        $child->getAlias(),
                        $this->getModelClassName($reflectionProperty, $constraintAttribute),
                    ),
                    JoinType::LEFT,
                ))
            ;
            $where = $constraintAttribute->getWhere();

            if ($where !== null) {
                $selectQuery->addWhere(new Where($where, $constraintAttribute->getWhereParameters()));
            }

            foreach ($child->getWheres() as $where) {
                $selectQuery->addWhere($where);
            }

            $this->extend($selectQuery, $parentModelClassName, $child->getChildren());
            $selects[] = new Select($table, $child->getAlias(), $child->getPrefix());
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
            ?? (mb_strtolower(preg_replace('/([A-Z])/', '_$1', $reflectionProperty->getName())) . '_id')
        ;
    }

    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
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

        if ($typeName === 'array') {
            return $constraintAttribute->getParentColumn() . '_id';
        }

        return $constraintAttribute->getParentColumn();
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
