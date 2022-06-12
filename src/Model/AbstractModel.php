<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\DateTimeFactory;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use mysqlDatabase;
use mysqlField;
use mysqlRegistry;
use mysqlTable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Throwable;

abstract class AbstractModel implements ModelInterface
{
    use ConstraintTrait;

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

    /**
     * @deprecated
     *
     * @throws ReflectionException
     * @throws JsonException
     */
    public function loadFromMysqlTable(mysqlTable $mysqlTable): void
    {
        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);
            $setter = 'set' . $fieldName;

            if (!method_exists($this, $setter)) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($fieldObject->getValue() === null) {
                $this->$setter($fieldObject->getValue());
            } else {
                switch ($this->getColumnType($fieldObject->getType())) {
                    case self::TYPE_INT:
                        try {
                            $this->$setter((int) $fieldObject->getValue());
                        } catch (Throwable) {
                            $this->$setter((bool) $fieldObject->getValue());
                        }

                        break;
                    case self::TYPE_FLOAT:
                        $this->$setter((float) $fieldObject->getValue());

                        break;
                    case self::TYPE_DATE_TIME:
                        $this->$setter($this->dateTime->get(
                            strtoupper((string) $fieldObject->getValue()) === 'CURRENT_TIMESTAMP()' ? 'now' : (string) $fieldObject->getValue()
                        ));

                        break;
                    default:
                        $reflectionParameter = (new ReflectionClass($this::class))
                            ->getMethod($setter)
                            ->getParameters()[0]
                        ;
                        /** @psalm-suppress UndefinedMethod */
                        $typeName = $reflectionParameter->getType()?->getName();

                        if ($typeName === 'array') {
                            $this->$setter(JsonUtility::decode((string) $fieldObject->getValue()));

                            break;
                        }

                        if (enum_exists($typeName)) {
                            $this->$setter(constant(sprintf(
                                '%s::%s',
                                $typeName,
                                $fieldObject->getValue() ?? ''
                            )));

                            break;
                        }

                        $this->$setter($fieldObject->getValue());

                        break;
                }
            }
        }
    }

    /**
     * @deprecated
     *
     * @throws JsonException
     */
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
            } elseif (is_object($value) && enum_exists($value::class)) {
                $fieldObject->setValue($value->value);
            } elseif (is_array($value)) {
                $fieldObject->setValue(JsonUtility::encode($value));
            } else {
                $fieldObject->setValue(is_bool($value) ? (int) $value : $value);
            }
        }
    }

    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }

    private function getColumnType(string $type): string
    {
        return self::COLUMN_TYPES[preg_replace('/^(\\w*).*$/', '$1', $type)];
    }
}
