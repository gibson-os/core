<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use Throwable;

trait ConstraintTrait
{
    private array $loadedConstraints = [];

    /**
     * @param array<AbstractModel|AbstractModel[]|null> $arguments
     *
     * @throws \ReflectionException
     *
     * @return AbstractModel|AbstractModel[]|null
     */
    public function __call(string $name, array $arguments)
    {
        $methodType = mb_substr($name, 0, 3);
        $propertyName = lcfirst(mb_substr($name, 3));
        $reflectionClass = new \ReflectionClass($this::class);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        /** @psalm-suppress UndefinedMethod */
        $propertyTypeName = $reflectionProperty->getType()?->getName();
        $constraintAttribute = $this->getConstraintAttribute($reflectionProperty);

        if ($propertyTypeName === 'array') {
            $models = [];

            if ($methodType !== 'get') {
                if (!is_array($arguments[0])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Argument for "set%s" is no array',
                        ucfirst($propertyName)
                    ));
                }

                $models = $arguments[0];
            }

            return match ($methodType) {
                'get' => $this->getConstraints($constraintAttribute, $reflectionProperty),
                'set' => $this->setConstraints($constraintAttribute, $reflectionProperty, $models),
                'add' => $this->addConstraints($constraintAttribute, $reflectionProperty, $models),
                'unload' => $this->unloadConstraint($propertyName),
            };
        }

        return match ($methodType) {
            'get' => $this->getConstraint($constraintAttribute, $reflectionProperty, $propertyTypeName),
            'set' => $this->setConstraint(
                $constraintAttribute,
                $reflectionProperty,
                $arguments[0] instanceof AbstractModel || ($reflectionProperty->getType()?->allowsNull() && $arguments[0] === null)
                    ? $arguments[0]
                    : throw new \InvalidArgumentException(sprintf(
                        'Argument for "set%s" is no instance of "%s"',
                        ucfirst($propertyName),
                        AbstractModel::class
                    ))
            ),
            'unload' => $this->unloadConstraint($propertyName),
        };
    }

    private function unloadConstraint(string $propertyName): AbstractModel
    {
        if (array_key_exists($propertyName, $this->loadedConstraints)) {
            unset($this->loadedConstraints[$propertyName]);
        }

        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    private function getConstraintAttribute(\ReflectionProperty $reflectionProperty): Constraint
    {
        $constraintAttributes = $reflectionProperty->getAttributes(
            Constraint::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        if (count($constraintAttributes) === 0) {
            throw new \ReflectionException(sprintf(
                'Property "%s" has no "%s" attribute!',
                $reflectionProperty->getName(),
                Constraint::class
            ));
        }

        /** @var Constraint $constraintAttribute */
        $constraintAttribute = $constraintAttributes[0]->newInstance();

        return $constraintAttribute;
    }

    /**
     * @throws \ReflectionException
     */
    private function getConstraint(
        Constraint $constraintAttribute,
        \ReflectionProperty $reflectionProperty,
        string $propertyTypeName
    ): ?AbstractModel {
        $parentColumn = $constraintAttribute->getParentColumn();
        $fieldName = $this->transformFieldName($parentColumn);
        $propertyName = $reflectionProperty->getName();
        $ownColumn = $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id');
        $value = $this->getConstraintValue($reflectionProperty, 'get' . ucfirst($ownColumn));

        if (array_key_exists($propertyName, $this->loadedConstraints) && $this->loadedConstraints[$propertyName] === $value) {
            return $this->$propertyName;
        }

        $parentModelClassName = $constraintAttribute->getParentModelClassName() ?? $propertyTypeName;
        /** @var AbstractModel $parentModel */
        $parentModel = new $parentModelClassName($this->database);

        if ($value === null) {
            $this->$propertyName = $reflectionProperty->getType()?->allowsNull() ? null : $parentModel;
            $this->loadedConstraints[$propertyName] = $value;

            return $this->$propertyName;
        }

        try {
            $propertyValue = $this->$propertyName;
        } catch (Throwable) {
            $propertyValue = null;
        }

        if (
            !$propertyValue instanceof $parentModelClassName ||
            $parentModel->{'get' . $fieldName}() !== $value
        ) {
            $this->$propertyName = $this->loadForeignRecord(
                $parentModel,
                $value,
                $parentColumn,
                $constraintAttribute->getWhere(),
                $constraintAttribute->getWhereParameters()
            ) ?? ($reflectionProperty->getType()?->allowsNull() ? null : $parentModel);
        }

        $this->loadedConstraints[$propertyName] = $value;

        return $this->$propertyName;
    }

    /**
     * @throws \ReflectionException
     *
     * @return AbstractModel[]
     */
    private function getConstraints(Constraint $constraintAttribute, \ReflectionProperty $reflectionProperty): array
    {
        $ownColumn = $constraintAttribute->getOwnColumn() ?? 'id';
        $value = $this->{'get' . ucfirst($ownColumn)}();
        $propertyName = $reflectionProperty->getName();

        if (array_key_exists($propertyName, $this->loadedConstraints) && $this->loadedConstraints[$propertyName] === $value) {
            return $this->$propertyName;
        }

        $parentModelClassName = $constraintAttribute->getParentModelClassName();

        if ($parentModelClassName === null) {
            throw new \ReflectionException(sprintf(
                'Property "parentModelClassName" of constraint attribute for property "%s::%s" is not set!',
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName()
            ));
        }

        /** @var AbstractModel $parentModel */
        $parentModel = new $parentModelClassName($this->database);
        $this->$propertyName = [];
        $this->loadedConstraints[$propertyName] = $value;

        if ($value !== null) {
            $this->addConstraints($constraintAttribute, $reflectionProperty, $this->loadForeignRecords(
                $parentModelClassName,
                $value,
                $parentModel->getTableName(),
                $constraintAttribute->getParentColumn() . '_id',
                $constraintAttribute->getWhere(),
                $constraintAttribute->getWhereParameters(),
                $constraintAttribute->getOrderBy()
            ));
        }

        return $this->$propertyName;
    }

    /**
     * @throws \Throwable
     */
    private function setConstraint(
        Constraint $constraintAttribute,
        \ReflectionProperty $reflectionProperty,
        ?AbstractModel $model
    ): AbstractModel {
        $propertyName = $reflectionProperty->getName();
        $ownColumn = ucfirst(
            $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id')
        );
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $model?->{'get' . ucfirst($fieldName)}();

        try {
            $this->{'set' . ucfirst($ownColumn)}($value);
        } catch (\Throwable $exception) {
            if ($value !== null) {
                throw $exception;
            }

            unset($this->$ownColumn);
        }

        $this->$propertyName = $model;
        $this->loadedConstraints[$propertyName] = $value;

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws \ReflectionException
     */
    private function setConstraints(
        Constraint $constraintAttribute,
        \ReflectionProperty $reflectionProperty,
        array $models
    ): self {
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id')
        );
        $this->setRelations($models, $fieldName, $value);

        $propertyName = $reflectionProperty->getName();
        $this->$propertyName = $models;
        $this->loadedConstraints[$propertyName] = $value;

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws \ReflectionException
     */
    private function addConstraints(
        Constraint $constraintAttribute,
        \ReflectionProperty $reflectionProperty,
        array $models
    ): self {
        $this->getConstraints($constraintAttribute, $reflectionProperty);
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id')
        );
        $this->setRelations($models, $fieldName, $value);

        $propertyName = $reflectionProperty->getName();
        array_push($this->$propertyName, ...$models);

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws \ReflectionException
     */
    private function setRelations(array $models, string $fieldName, mixed $value): void
    {
        foreach ($models as $model) {
            $model->{'set' . $fieldName}($this);

            if ($value !== null) {
                $model->{'set' . $fieldName . 'Id'}($value);

                continue;
            }

            $reflectionClass = new \ReflectionClass($model);
            $reflectionIdProperty = $reflectionClass->getProperty(lcfirst($fieldName) . 'Id');

            if (!$reflectionIdProperty->isInitialized($model)) {
                continue;
            }

            if (!$reflectionIdProperty->hasDefaultValue()) {
                throw new \ReflectionException(sprintf(
                    'Property "%s" of class "%s" is initialized an has no default value!',
                    $reflectionIdProperty->getName(),
                    $model::class
                ));
            }

            $value = $reflectionIdProperty->getDefaultValue();
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function getConstraintValue(\ReflectionProperty $reflectionProperty, string $getterName): float|int|string|null
    {
        $reflectionMethod = $reflectionProperty->getDeclaringClass()->getMethod($getterName);

        if (!$reflectionMethod->isPublic()) {
            throw new \ReflectionException(sprintf('Method "%s" is not public!', $getterName));
        }

        try {
            return $this->{$getterName}();
        } catch (Throwable) {
            return null;
        }
    }

    private function loadForeignRecord(
        AbstractModel $model,
        string|int|float $value,
        string $foreignField = 'id',
        string $where = null,
        array $whereParameters = []
    ): ?AbstractModel {
        $mysqlTable = new \mysqlTable($this->database, $model->getTableName());
        $mysqlTable
            ->setWhere('`' . $foreignField . '`=?' . ($where === null ? '' : ' AND (' . $where . ')'))
            ->setWhereParameters(array_merge([$value], $whereParameters))
            ->setLimit(1)
        ;

        if (!$mysqlTable->selectPrepared()) {
            return null;
        }

        $model->loadFromMysqlTable($mysqlTable);

        return $model;
    }

    /**
     * @param class-string<AbstractModel> $modelClassName
     *
     * @return AbstractModel[]
     */
    private function loadForeignRecords(
        string $modelClassName,
        string|int|float|null $value,
        string $foreignTable,
        string $foreignField,
        string $where = null,
        array $whereParameters = [],
        string $orderBy = null
    ): array {
        $models = [];

        if ($value === null) {
            return $models;
        }

        $mysqlTable = (new \mysqlTable($this->database, $foreignTable))
            ->setWhere('`' . $foreignField . '`=?' . ($where === null ? '' : ' AND (' . $where . ')'))
            ->setWhereParameters(array_merge([$value], $whereParameters))
            ->setOrderBy($orderBy)
        ;

        if (!$mysqlTable->selectPrepared()) {
            return $models;
        }

        do {
            $model = new $modelClassName($this->database);
            $model->loadFromMysqlTable($mysqlTable);
            $models[] = $model;
        } while ($mysqlTable->next());

        return $models;
    }
}
