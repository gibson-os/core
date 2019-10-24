<?php
namespace GibsonOS\Core\Model;

use DateTime;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\Registry;
use mysqlDatabase;
use mysqlField;
use mysqlTable;

abstract class AbstractModel implements ModelInterface
{
    /**
     * @var mysqlDatabase
     */
    private $database;

    private const TYPE_INT = 'int';
    private const TYPE_FLOAT = 'int';
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
    ];

    /**
     * AbstractModel constructor.
     * @param mysqlDatabase|null $database
     */
    public function __construct(mysqlDatabase $database = null)
    {
        if (is_null($database)) {
            $this->database = Registry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }
    }

    /**
     * @return mysqlTable
     */
    public function getMysqlTable(): mysqlTable
    {
        $mysqlTable = new mysqlTable($this->database, $this->getTableName());
        $this->loadFromMysqlTable($mysqlTable);

        return $mysqlTable;
    }

    /**
     * @param mysqlTable $mysqlTable
     */
    public function loadFromMysqlTable(mysqlTable $mysqlTable)
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
                        $this->{'set' . $fieldName}((int)$fieldObject->getValue());
                        break;
                    case self::TYPE_FLOAT:
                        $this->{'set' . $fieldName}((float)$fieldObject->getValue());
                        break;
                    case self::TYPE_DATE_TIME:
                        $this->{'set' . $fieldName}(new DateTime(
                            $fieldObject->getValue() === 'CURRENT_TIMESTAMP' ? 'now' : $fieldObject->getValue()
                        ));
                        break;
                    default:
                        $this->{'set' . $fieldName}($fieldObject->getValue());
                        break;
                }
            }
        }
    }

    /**
     * @param mysqlTable $mysqlTable
     */
    public function setToMysqlTable(mysqlTable $mysqlTable)
    {
        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);

            if (!method_exists($this, 'get' . $fieldName)) {
                continue;
            }

            if (is_null($this->{'get' . $fieldName}())) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($this->getColumnType($fieldObject->getType()) === self::TYPE_DATE_TIME) {
                $fieldObject->setValue($this->{'get' . $fieldName}()->format('Y-m-d H:i:s'));
                $this->{'set' . $fieldName}(new DateTime($fieldObject->getValue()));
            } else {
                $fieldObject->setValue($this->{'get' . $fieldName}());
            }

        }
    }

    /**
     * @param mysqlTable|null $mysqlTable
     * @throws SaveError
     */
    public function save(mysqlTable $mysqlTable = null)
    {
        if (is_null($mysqlTable)) {
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
     * @param null|mysqlTable $mysqlTable
     * @throws DeleteError
     */
    public function delete(mysqlTable $mysqlTable = null)
    {
        if (is_null($mysqlTable)) {
            $mysqlTable = new mysqlTable($this->database, $this->getTableName());
        }

        $this->setToMysqlTable($mysqlTable);

        if (!$mysqlTable->delete()) {
            $exception = new DeleteError();
            $exception->setModel($this);
            throw $exception;
        }
    }

    /**
     * @param string $fieldName
     * @return string
     */
    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }

    /**
     * @param AbstractModel $model
     * @param mixed $value
     * @param string $foreignField
     * @throws SelectError
     */
    protected function loadForeignRecord(AbstractModel $model, $value, string $foreignField = 'id')
    {
        $fieldName = $this->transformFieldName($foreignField);

        if ($model->{'get' . $fieldName}() == $value) {
            return;
        }

        $mysqlTable = new mysqlTable($this->database, $model->getTableName());
        $mysqlTable->setWhere('`' . $foreignField . '`=' . $this->database->escape($value));
        $mysqlTable->setLimit(1);

        if (!$mysqlTable->select()) {
            $exception = new SelectError('Fremd Eintrag `' . $model->getTableName() . '`.`' . $foreignField . '`=' . $value . ' konnte nicht geladen werden!');
            $exception->setTable($mysqlTable);

            throw $exception;
        }

        $model->loadFromMysqlTable($mysqlTable);
    }

    /**
     * @param string $modelClassName
     * @param mixed $value
     * @param string $foreignTable
     * @param string $foreignField
     * @return AbstractModel[]
     */
    protected function loadForeignRecords(string $modelClassName, $value, string $foreignTable, string $foreignField): array
    {
        $mysqlTable = new mysqlTable($this->database, $foreignTable);
        $mysqlTable->setWhere('`' . $foreignField . '`=' . $this->database->escape($value));

        $models = [];

        if (!$mysqlTable->select()) {
            return $models;
        }

        do {
            /**
             * @var AbstractModel $model
             */
            $model = new $modelClassName();
            $model->loadFromMysqlTable($mysqlTable);
            $models[] = $model;
        } while ($mysqlTable->next());

        return $models;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getColumnType(string $type): string
    {
        return self::COLUMN_TYPES[preg_replace('/^(\\w*).*$/', '$1', $type)];
    }
}