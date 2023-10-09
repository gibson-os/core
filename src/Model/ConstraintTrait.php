<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use InvalidArgumentException;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

trait ConstraintTrait
{
    private array $loadedConstraints = [];

    /**
     * @param array<AbstractModel|AbstractModel[]|null> $arguments
     *
     * @throws Throwable
     * @throws ReflectionException
     *
     * @return AbstractModel|AbstractModel[]|null
     */
    public function __call(string $name, array $arguments)
    {
        $methodType = preg_replace('/^(get|set|add|unload).*/', '$1', $name, 1);
        $propertyName = lcfirst(preg_replace('/^(get|set|add|unload)/', '', $name, 1));
        $reflectionClass = new ReflectionClass($this::class);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        /** @psalm-suppress UndefinedMethod */
        $propertyTypeName = $reflectionProperty->getType()?->getName();
        $constraintAttribute = $this->getConstraintAttribute($reflectionProperty);

        if ($propertyTypeName === 'array') {
            $models = [];

            if ($methodType === 'set' || $methodType === 'add') {
                if (!is_array($arguments[0])) {
                    throw new InvalidArgumentException(sprintf(
                        'Argument for "set%s" is no array',
                        ucfirst($propertyName),
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
                    : throw new InvalidArgumentException(sprintf(
                        'Argument for "set%s" is no instance of "%s"',
                        ucfirst($propertyName),
                        AbstractModel::class,
                    )),
            ),
            'unload' => $this->unloadConstraint($propertyName),
        };
    }

    public function isConstraintLoaded(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->loadedConstraints);
    }

    private function unloadConstraint(string $propertyName): AbstractModel
    {
        if ($this->isConstraintLoaded($propertyName)) {
            unset($this->loadedConstraints[$propertyName]);
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function getConstraintAttribute(ReflectionProperty $reflectionProperty): Constraint
    {
        $constraintAttributes = $reflectionProperty->getAttributes(
            Constraint::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        if (count($constraintAttributes) === 0) {
            throw new ReflectionException(sprintf(
                'Property "%s" has no "%s" attribute!',
                $reflectionProperty->getName(),
                Constraint::class,
            ));
        }

        /** @var Constraint $constraintAttribute */
        $constraintAttribute = $constraintAttributes[0]->newInstance();

        return $constraintAttribute;
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function getConstraint(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        string $propertyTypeName,
    ): ?AbstractModel {
        $parentColumn = $constraintAttribute->getParentColumn();
        $fieldName = $this->transformFieldName($parentColumn);
        $propertyName = $reflectionProperty->getName();
        $ownColumn = $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id');
        $value = $this->getConstraintValue($reflectionProperty, 'get' . ucfirst($ownColumn));

        if ($this->isConstraintLoaded($propertyName) && $this->loadedConstraints[$propertyName] === $value) {
            return $this->$propertyName;
        }

        $parentModelClassName = $constraintAttribute->getParentModelClassName() ?? $propertyTypeName;
        /** @var AbstractModel $parentModel */
        $parentModel = new $parentModelClassName($this->modelWrapper);

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
            !$propertyValue instanceof $parentModelClassName
            || $parentModel->{'get' . $fieldName}() !== $value
        ) {
            $this->$propertyName = $this->loadForeignRecord(
                $parentModel,
                $value,
                $parentColumn,
                $constraintAttribute->getWhere(),
                $constraintAttribute->getWhereParameters(),
            ) ?? ($reflectionProperty->getType()?->allowsNull() ? null : $parentModel);
        }

        $this->loadedConstraints[$propertyName] = $value;

        return $this->$propertyName;
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return AbstractModel[]
     */
    private function getConstraints(Constraint $constraintAttribute, ReflectionProperty $reflectionProperty): array
    {
        $ownColumn = $constraintAttribute->getOwnColumn() ?? 'id';
        $value = $this->{'get' . ucfirst($ownColumn)}();
        $propertyName = $reflectionProperty->getName();

        if ($this->isConstraintLoaded($propertyName) && $this->loadedConstraints[$propertyName] === $value) {
            return $this->$propertyName;
        }

        $parentModelClassName = $constraintAttribute->getParentModelClassName();

        if ($parentModelClassName === null) {
            throw new ReflectionException(sprintf(
                'Property "parentModelClassName" of constraint attribute for property "%s::%s" is not set!',
                $reflectionProperty->getDeclaringClass()->getName(),
                $reflectionProperty->getName(),
            ));
        }

        /** @var AbstractModel $parentModel */
        $parentModel = new $parentModelClassName($this->modelWrapper);
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
                $constraintAttribute->getOrderBy(),
            ));
        }

        return $this->$propertyName;
    }

    /**
     * @throws Throwable
     */
    private function setConstraint(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        ?AbstractModel $model,
    ): AbstractModel {
        $propertyName = $reflectionProperty->getName();
        $ownColumn = ucfirst(
            $this->transformFieldName($constraintAttribute->getOwnColumn() ?? $propertyName . 'Id'),
        );
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $model?->{'get' . ucfirst($fieldName)}();

        try {
            $this->{'set' . ucfirst($ownColumn)}($value);
        } catch (Throwable $exception) {
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
     * @throws ReflectionException
     */
    private function setConstraints(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        array $models,
    ): self {
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id'),
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
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function addConstraints(
        Constraint $constraintAttribute,
        ReflectionProperty $reflectionProperty,
        array $models,
    ): self {
        $this->getConstraints($constraintAttribute, $reflectionProperty);
        $fieldName = $this->transformFieldName($constraintAttribute->getParentColumn());
        $value = $this->getConstraintValue(
            $reflectionProperty,
            'get' . ucfirst($constraintAttribute->getOwnColumn() ?? 'id'),
        );
        $this->setRelations($models, $fieldName, $value);

        $propertyName = $reflectionProperty->getName();
        array_push($this->$propertyName, ...$models);

        return $this;
    }

    /**
     * @param AbstractModel[] $models
     *
     * @throws ReflectionException
     */
    private function setRelations(array $models, string $fieldName, mixed $value): void
    {
        foreach ($models as $model) {
            $model->{'set' . $fieldName}($this);

            if ($value !== null) {
                $model->{'set' . $fieldName . 'Id'}($value);

                continue;
            }

            $reflectionClass = new ReflectionClass($model);
            $reflectionIdProperty = $reflectionClass->getProperty(lcfirst($fieldName) . 'Id');

            if (!$reflectionIdProperty->isInitialized($model)) {
                continue;
            }

            if (!$reflectionIdProperty->hasDefaultValue()) {
                throw new ReflectionException(sprintf(
                    'Property "%s" of class "%s" is initialized an has no default value!',
                    $reflectionIdProperty->getName(),
                    $model::class,
                ));
            }

            $value = $reflectionIdProperty->getDefaultValue();
        }
    }

    /**
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

    /**
     * @throws ReflectionException
     * @throws JsonException
     * @throws ClientException
     */
    private function loadForeignRecord(
        AbstractModel $model,
        string|int|float $value,
        string $foreignField = 'id',
        string $where = null,
        array $whereParameters = [],
    ): ?AbstractModel {
        $modelWrapper = $this->getModelWrapper();
        $table = $modelWrapper->getTableManager()->getTable($model->getTableName());
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where(
                sprintf('`%s`=?%s', $foreignField, $where === null ? '' : ' AND (' . $where . ')'),
                array_merge([$value], $whereParameters),
            ))
            ->setLimit(1)
        ;

        $result = $modelWrapper->getClient()->execute($selectQuery);
        $modelWrapper->getModelManager()->loadFromRecord($result->iterateRecords()->current(), $model);

        return $model;
    }

    /**
     * @param class-string<AbstractModel>   $modelClassName
     * @param array<string, OrderDirection> $orderBy
     *
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
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
        array $orderBy = [],
    ): array {
        $models = [];

        if ($value === null) {
            return $models;
        }

        $modelWrapper = $this->getModelWrapper();
        $table = $modelWrapper->getTableManager()->getTable($foreignTable);
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where(
                sprintf('`%s`=?%s', $foreignField, $where === null ? '' : ' AND (' . $where . ')'),
                array_merge([$value], $whereParameters),
            ))
            ->setOrders($orderBy)
        ;

        $result = $modelWrapper->getClient()->execute($selectQuery);

        foreach ($result->iterateRecords() as $record) {
            $model = new $modelClassName($modelWrapper);
            $modelWrapper->getModelManager()->loadFromRecord($record, $model);
            $models[] = $model;
        }

        return $models;
    }
}
