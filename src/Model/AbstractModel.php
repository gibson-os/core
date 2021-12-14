<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use Exception;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Factory\DateTimeFactory;
use GibsonOS\Core\Service\DateTimeService;
use mysqlDatabase;
use mysqlField;
use mysqlRegistry;
use mysqlTable;
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

    protected function loadForeignRecord(AbstractModel $model, string|int|float $value, string $foreignField = 'id'): void
    {
        $fieldName = $this->transformFieldName($foreignField);

        if ($model->{'get' . $fieldName}() === $value) {
            return;
        }

        $mysqlTable = new mysqlTable($this->database, $model->getTableName());
        $mysqlTable
            ->setWhere('`' . $foreignField . '`=?')
            ->addWhereParameter($value)
            ->setLimit(1)
        ;

        if (!$mysqlTable->selectPrepared()) {
            return;
        }

        $model->loadFromMysqlTable($mysqlTable);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @return T[]
     */
    protected function loadForeignRecords(
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

    protected function setForeignValueByModelOrId(
        AbstractModel|null $model,
        null|string|int $id,
        callable $setModelFunction,
        callable $setIdFunction
    ): void {
        if ($model !== null) {
            $setModelFunction($model);
        } elseif ($id !== null) {
            $setIdFunction($id);
        } else {
            $setModelFunction(null);
            $setIdFunction(null);
        }
    }

    private function getColumnType(string $type): string
    {
        return self::COLUMN_TYPES[preg_replace('/^(\\w*).*$/', '$1', $type)];
    }
}
