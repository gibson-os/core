<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use InvalidArgumentException;
use mysqlTable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

trait ConstraintTrait
{
    private array $loadedConstraints = [];

    /**
     * @param AbstractModel[]|AbstractModel[][] $arguments
     *
     * @throws ReflectionException
     *
     * @return AbstractModel|AbstractModel[]|null
     */
    public function __call(string $name, array $arguments)
    {
        $methodType = mb_substr($name, 0, 3);
        $propertyName = lcfirst(mb_substr($name, 3));
        $reflectionClass = new ReflectionClass($this::class);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        /** @psalm-suppress UndefinedMethod */
        $propertyTypeName = $reflectionProperty->getType()?->getName();
        $constraintAttribute = $this->getConstraintAttribute($reflectionProperty);

        if ($propertyTypeName === 'array') {
            $models = [];

            if ($methodType !== 'get') {
                if (!is_array($arguments[0])) {
                    throw new InvalidArgumentException(sprintf(
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
            };
        }

        return match ($methodType) {
            'get' => $this->getConstraint($constraintAttribute, $reflectionProperty, $propertyTypeName),
            'set' => $this->setConstraint(
                $constraintAttribute,
                $reflectionProperty,
                $arguments[0] instanceof AbstractModel
                    ? $arguments[0]
                    : throw new InvalidArgumentException(sprintf(
                        'Argument for "set%s" is no instance of "%s"',
                        ucfirst($propertyName),
                        AbstractModel::class
                    ))
            ),
        };
    }

    /**
     * @throws ReflectionException
     */
    private function getConstraintAttribute(ReflectionProperty $reflectionProperty): Constraint
    {
        $constraintAttributes = $reflectionProperty->getAttributes(
            Constraint::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (count($constraintAttributes) === 0) {
            throw new ReflectionException(sprintf(
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
     * @throws ReflectionException
     */
    private function getConstraint(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        string $propertyTypeName
    ): ?AbstractModel {
        $parentColumn = $constraintAttribute->getParentColumn();
        $fieldName = $this->transformFieldName($parentColumn);
        $propertyName = $reflectionProperty->getName();
        $ownColumn = $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id');
        $value = $this->getConstraintValue($reflectionProperty, 'get' . ucfirst($ownColumn));

        if (isset($this->loadedConstraints[$propertyName]) && $this->loadedConstraints[$propertyName] === $value) {
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
            $this->$propertyName = $this->loadForeignRecord($parentModel, $value, $parentColumn)
                ?? ($reflectionProperty->getType()?->allowsNull() ? null : $parentModel);
        }

        $this->loadedConstraints[$propertyName] = $value;

        return $this->$propertyName;
    }

    /**
     * @throws ReflectionException
     *
     * @return AbstractModel[]
     */
    private function getConstraints(Constraint $constraintAttribute, ReflectionProperty $reflectionProperty): array
    {
        $ownColumn = $constraintAttribute->getOwnColumn() ?? 'id';
        $value = $this->{'get' . ucfirst($ownColumn)}();
        $propertyName = $reflectionProperty->getName();

        if (isset($this->loadedConstraints[$propertyName]) && $this->loadedConstraints[$propertyName] === $value) {
            return $this->$propertyName;
        }

        $parentModelClassName = $constraintAttribute->getParentModelClassName();

        if ($parentModelClassName === null) {
            throw new ReflectionException(sprintf(
                'Property "parentModelClassName" of constraint attribute for property "%s::%s" is not set!',
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName()
            ));
        }

        /** @var AbstractModel $parentModel */
        $parentModel = new $parentModelClassName($this->database);
        $this->$propertyName = [];
        $this->loadedConstraints[$propertyName] = $value;
        $this->addConstraints($constraintAttribute, $reflectionProperty, $this->loadForeignRecords(
            $parentModelClassName,
            $value,
            $parentModel->getTableName(),
            $constraintAttribute->getParentColumn() . '_id'
        ));

        return $this->$propertyName;
    }

    private function setConstraint(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        AbstractModel $model
    ): AbstractModel {
        $propertyName = $reflectionProperty->getName();
        $ownColumn = ucfirst(
            $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id')
        );
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $model->{'get' . ucfirst($fieldName)}();
        $this->{'set' . ucfirst($ownColumn)}($value);
        $this->$propertyName = $model;
        $this->loadedConstraints[$propertyName] = $value;

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws ReflectionException
     */
    private function setConstraints(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        array $models
    ): self {
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id')
        );

        foreach ($models as $model) {
            $model->{'set' . $fieldName . 'Id'}($value);
            $model->{'set' . $fieldName}($this);
        }

        $propertyName = $reflectionProperty->getName();
        $this->$propertyName = $models;
        $this->loadedConstraints[$propertyName] = $value;

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws ReflectionException
     */
    private function addConstraints(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        array $models
    ): self {
        $this->getConstraints($constraintAttribute, $reflectionProperty);
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $propertyName = $reflectionProperty->getName();
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id')
        );

        foreach ($models as $model) {
            $model->{'set' . $fieldName . 'Id'}($value);
            $model->{'set' . $fieldName}($this);
            $this->$propertyName[] = $model;
        }

        return $this;
    }

    /**
     * @param mixed $gettterName
     * @param mixed $getterName
     *
     * @throws ReflectionException
     */
    private function getConstraintValue(ReflectionProperty $reflectionProperty, string $getterName): float|int|string|null
    {
        $reflectionMethod = $reflectionProperty->getDeclaringClass()->getMethod($getterName);

        if (!$reflectionMethod->isPublic()) {
            throw new ReflectionException(sprintf('Method "%s" is not public!', $getterName));
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
        string $foreignField = 'id'
    ): ?AbstractModel {
        $mysqlTable = new mysqlTable($this->database, $model->getTableName());
        $mysqlTable
            ->setWhere('`' . $foreignField . '`=?')
            ->addWhereParameter($value)
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
        string $foreignField
    ): array {
        $models = [];

        if ($value === null) {
            return $models;
        }

        $mysqlTable = new mysqlTable($this->database, $foreignTable);
        $mysqlTable
            ->setWhere('`' . $foreignField . '`=?')
            ->addWhereParameter($value)
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
