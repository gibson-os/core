<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use Exception;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Factory\DateTimeFactory;
use GibsonOS\Core\Service\DateTimeService;
use mysqlDatabase;
use mysqlField;
use mysqlRegistry;
use mysqlTable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;

abstract class AbstractModel implements ModelInterface
{
    private mysqlDatabase $database;

    private const TYPE_INT = 'int';

    private const TYPE_FLOAT = 'float';

    private const TYPE_STRING = 'string';

    private const TYPE_DATE_TIME = 'dateTime';

    private const COLUMN_TYPES = [
        'tinyint' => self::TYPE_INT,
        'smallint' => self::TYPE_INT,
        'int' => self::TYPE_INT,
        'bigint' => self::TYPE_INT,
        'time' => self::TYPE_DATE_TIME,
        'date' => self::TYPE_DATE_TIME,
        'datetime' => self::TYPE_DATE_TIME,
        'timestamp' => self::TYPE_DATE_TIME,
        'varchar' => self::TYPE_STRING,
        'enum' => self::TYPE_STRING,
        'text' => self::TYPE_STRING,
        'longtext' => self::TYPE_STRING,
        'binary' => self::TYPE_STRING,
        'varbinary' => self::TYPE_STRING,
        'float' => self::TYPE_FLOAT,
        'decimal' => self::TYPE_FLOAT,
    ];

    private DateTimeService $dateTime;

    private ?string $tableName = null;

    private array $loadedConstraints = [];

    /**
     * @throws GetError
     */
    public function __construct(mysqlDatabase $database = null)
    {
        $this->dateTime = DateTimeFactory::get();

        if ($database === null) {
            $this->database = mysqlRegistry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }
    }

    /**
     * @param ModelInterface[] $arguments
     *
     * @throws ReflectionException
     *
     * @return AbstractModel|AbstractModel[]|null
     */
    public function __call(string $name, array $arguments): mixed
    {
        $methodType = mb_substr($name, 0, 3);
        $propertyName = lcfirst(mb_substr($name, 3));

        $reflectionClass = new ReflectionClass($this::class);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        /** @psalm-suppress UndefinedMethod */
        $propertyTypeName = $reflectionProperty->getType()?->getName();
        $constraintAttributes = $reflectionProperty->getAttributes(
            Constraint::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        if (count($constraintAttributes) === 0) {
            return null;
        }

        /** @var Constraint $constraintAttribute */
        $constraintAttribute = $constraintAttributes[0]->newInstance();
        $parentModelClassName = $constraintAttribute->getParentModelClassName() ?? $propertyTypeName;
        $parentColumn = $constraintAttribute->getParentColumn();
        $fieldName = $this->transformFieldName($parentColumn);

        return match ($methodType) {
            'get' => $this->getConstraints(
                $constraintAttribute,
                $propertyName,
                $parentModelClassName,
                $propertyTypeName,
                $fieldName,
                $reflectionProperty
            ),
            'set' => '',
            'add' => $this->addConstraint(
                $constraintAttribute,
                $propertyName,
                $parentModelClassName,
                $propertyTypeName,
                $fieldName,
                $reflectionProperty,
                $arguments[0]
            ),
        };
    }

    /**
     * @param class-string<AbstractModel> $parentModelClassName
     *
     * @return AbstractModel|AbstractModel[]|null
     */
    private function getConstraints(
        Constraint $constraintAttribute,
        string $propertyName,
        string $parentModelClassName,
        string $propertyTypeName,
        string $fieldName,
        ReflectionProperty $reflectionProperty
    ): mixed {
        $getterName = 'get' . lcfirst($this->transformFieldName(
            $constraintAttribute->getOwnColumn() ?? $propertyName . 'Id'
        ));
        /** @var float|int|string $value */
        $value = $this->{$getterName}();
        $parentModel = new $parentModelClassName();
        $parentTable = $parentModel->getTableName();
        $parentColumn = $constraintAttribute->getParentColumn();

        if ($propertyTypeName === 'array') {
            $this->$propertyName = $this->loadForeignRecords(
                $parentModelClassName,
                $value,
                $parentTable,
                $parentColumn
            );

            return $this->$propertyName;
        }

        if ($value === null) {
            $this->$propertyName = $reflectionProperty->getType()?->allowsNull() ? null : $parentModel;

            return $this->$propertyName;
        }

        if (
            !$this->$propertyName instanceof $parentModelClassName ||
            $parentModel->{'get' . $fieldName}() !== $value
        ) {
            $this->$propertyName = $this->loadForeignRecord($parentModel, $value, $parentColumn)
                ?? ($reflectionProperty->getType()?->allowsNull() ? null : $parentModel)
            ;
        }

        return $this->$propertyName;
    }

    /**
     * @param class-string<AbstractModel> $parentModelClassName
     *
     * @return AbstractModel
     */
    private function addConstraint(
        Constraint $constraintAttribute,
        string $propertyName,
        string $parentModelClassName,
        string $propertyTypeName,
        string $fieldName,
        ReflectionProperty $reflectionProperty,
        ModelInterface $model
    ): self {
        if ($propertyTypeName !== 'array') {
            return $this;
        }

        $this->getConstraints(
            $constraintAttribute,
            $propertyName,
            $parentModelClassName,
            $propertyTypeName,
            $fieldName,
            $reflectionProperty
        );
        $this->$propertyName[] = $model;

        return $this;
    }

    private function setConstraint(
        Constraint $constraintAttribute,
        string $propertyName,
        string $parentModelClassName,
        string $propertyTypeName,
        string $fieldName,
        ReflectionProperty $reflectionProperty,
        ModelInterface $model
    ): self {
        // @todo einbauen

        return $this;
    }

    public function getMysqlTable(): mysqlTable
    {
        $mysqlTable = new mysqlTable($this->database, $this->getTableName());
        $this->loadFromMysqlTable($mysqlTable);

        return $mysqlTable;
    }

    public function getDatabase(): mysqlDatabase
    {
        return $this->database;
    }

    public function getTableName(): string
    {
        if ($this->tableName !== null) {
            return $this->tableName;
        }

        try {
            $reflectionClass = new ReflectionClass($this::class);
            $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);
            /** @var Table $tableAttribute */
            $tableAttribute = $tableAttributes[0]->newInstance();
            $this->tableName = $tableAttribute->getName() ?? $this->transformName(str_replace(
                '\\',
                '',
                str_replace(
                    'Core\\',
                    '',
                    preg_replace('/.*\\\\(.+?)\\\\.*Model\\\\/', '$1\\', $this::class)
                )
            ));
        } catch (ReflectionException) {
        }

        return $this->tableName ?? '';
    }

    private function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }

    public function loadFromMysqlTable(mysqlTable $mysqlTable): void
    {
        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);

            if (!method_exists($this, 'set' . $fieldName)) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($fieldObject->getValue() === null) {
                $this->{'set' . $fieldName}($fieldObject->getValue());
            } else {
                switch ($this->getColumnType($fieldObject->getType())) {
                    case self::TYPE_INT:
                        try {
                            $this->{'set' . $fieldName}((int) $fieldObject->getValue());
                        } catch (Throwable) {
                            $this->{'set' . $fieldName}((bool) $fieldObject->getValue());
                        }

                        break;
                    case self::TYPE_FLOAT:
                        $this->{'set' . $fieldName}((float) $fieldObject->getValue());

                        break;
                    case self::TYPE_DATE_TIME:
                        $this->{'set' . $fieldName}($this->dateTime->get(
                            strtoupper((string) $fieldObject->getValue()) === 'CURRENT_TIMESTAMP()' ? 'now' : (string) $fieldObject->getValue()
                        ));

                        break;
                    default:
                        $this->{'set' . $fieldName}($fieldObject->getValue());

                        break;
                }
            }
        }
    }

    public function setToMysqlTable(mysqlTable $mysqlTable): void
    {
        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);
            $possiblePrefixes = ['get', 'is', 'has', 'should'];
            $getterPrefix = null;

            foreach ($possiblePrefixes as $possiblePrefix) {
                if (method_exists($this, $possiblePrefix . $fieldName)) {
                    $getterPrefix = $possiblePrefix;

                    break;
                }
            }

            if ($getterPrefix === null) {
                continue;
            }

            $value = $this->{$getterPrefix . $fieldName}();

            if ($value === null) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($this->getColumnType($fieldObject->getType()) === self::TYPE_DATE_TIME) {
                $fieldObject->setValue($value->format('Y-m-d H:i:s'));
                $this->{'set' . $fieldName}($this->dateTime->get((string) $fieldObject->getValue()));
            } else {
                $fieldObject->setValue(is_bool($value) ? (int) $value : $value);
            }
        }
    }

    /**
     * @throws SaveError
     * @throws Exception
     */
    public function save(mysqlTable $mysqlTable = null): void
    {
        if ($mysqlTable === null) {
            $mysqlTable = new mysqlTable($this->database, $this->getTableName());
        }

        $this->setToMysqlTable($mysqlTable);

        if (!$mysqlTable->save()) {
            $exception = new SaveError();
            $exception->setModel($this);

            throw $exception;
        }

        $mysqlTable->getReplacedRecord();
        $this->loadFromMysqlTable($mysqlTable);
    }

    /**
     * @throws DeleteError
     */
    public function delete(mysqlTable $mysqlTable = null): void
    {
        if (null === $mysqlTable) {
            $mysqlTable = new mysqlTable($this->database, $this->getTableName());
        }

        $this->setToMysqlTable($mysqlTable);

        if (!$mysqlTable->deletePrepared()) {
            $exception = new DeleteError();
            $exception->setModel($this);

            throw $exception;
        }
    }

    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }

    private function loadForeignRecord(AbstractModel $model, string|int|float $value, string $foreignField = 'id'): ?AbstractModel
    {
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
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @return T[]
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
            $model = new $modelClassName();
            $model->loadFromMysqlTable($mysqlTable);
            $models[] = $model;
        } while ($mysqlTable->next());

        return $models;
    }

    private function getColumnType(string $type): string
    {
        return self::COLUMN_TYPES[preg_replace('/^(\\w*).*$/', '$1', $type)];
    }
}
