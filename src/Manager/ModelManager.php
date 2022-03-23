<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use Exception;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use mysqlDatabase;
use mysqlField;
use mysqlTable;
use ReflectionAttribute;
use ReflectionException;
use Throwable;

class ModelManager
{
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

    public function __construct(
        private mysqlDatabase $mysqlDatabase,
        private DateTimeService $dateTimeService,
        private JsonUtility $jsonUtility,
        private ReflectionManager $reflectionManager
    ) {
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function save(ModelInterface $model): void
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($model);
        $childrenModels = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $constraintAttribute = $this->reflectionManager->getAttribute(
                $reflectionProperty,
                Constraint::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if ($constraintAttribute === null) {
                continue;
            }

            $getter = 'get' . ucfirst($reflectionProperty->getName());

            if ($this->reflectionManager->getTypeName($reflectionProperty) !== 'array') {
                $setter = 'set' . ucfirst($reflectionProperty->getName());
                $model->$setter($model->$getter());

                continue;
            }

            foreach ($model->$getter() as $child) {
                $childrenModels[] = $child;
            }
        }

        $mysqlTable = $this->setToMysqlTable($model);

        try {
            $mysqlTable->save();
        } catch (Exception $exception) {
            $exception = new SaveError($exception->getMessage(), 0, $exception);
            $exception->setModel($model);

            throw $exception;
        }

        $mysqlTable->getReplacedRecord();
        $this->loadFromMysqlTable($mysqlTable, $model);

        foreach ($childrenModels as $childrenModel) {
            $this->save($childrenModel);
        }
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    public function delete(ModelInterface $model): void
    {
        $mysqlTable = $this->setToMysqlTable($model);

        if (!$mysqlTable->deletePrepared()) {
            $exception = new DeleteError();
            $exception->setModel($model);

            throw $exception;
        }
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function loadFromMysqlTable(mysqlTable $mysqlTable, ModelInterface $model): void
    {
        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);
            $setter = 'set' . $fieldName;

            if (!method_exists($model, $setter)) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($fieldObject->getValue() === null) {
                $model->$setter($fieldObject->getValue());
            } else {
                switch ($this->getColumnType($fieldObject->getType())) {
                    case self::TYPE_INT:
                        try {
                            $model->$setter((int) $fieldObject->getValue());
                        } catch (Throwable) {
                            $model->$setter((bool) $fieldObject->getValue());
                        }

                        break;
                    case self::TYPE_FLOAT:
                        $model->$setter((float) $fieldObject->getValue());

                        break;
                    case self::TYPE_DATE_TIME:
                        $model->$setter($this->dateTimeService->get(
                            strtoupper((string) $fieldObject->getValue()) === 'CURRENT_TIMESTAMP()' ? 'now' : (string) $fieldObject->getValue()
                        ));

                        break;
                    default:
                        $reflectionParameter = $this->reflectionManager->getReflectionClass($model)
                            ->getMethod($setter)
                            ->getParameters()[0]
                        ;
                        /** @psalm-suppress UndefinedMethod */
                        $typeName = $reflectionParameter->getType()?->getName();

                        if ($typeName === 'array') {
                            $model->$setter($this->jsonUtility->decode((string) $fieldObject->getValue()));

                            break;
                        }

                        if (enum_exists($typeName)) {
                            $model->$setter($typeName::from($fieldObject->getValue()));

                            break;
                        }

                        $model->$setter($fieldObject->getValue());

                        break;
                }
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function setToMysqlTable(ModelInterface $model): mysqlTable
    {
        $mysqlTable = new mysqlTable($this->mysqlDatabase, $model->getTableName());

        foreach ($mysqlTable->fields as $field) {
            $fieldName = $this->transformFieldName($field);
            $possiblePrefixes = ['get', 'is', 'has', 'should'];
            $getterPrefix = null;

            foreach ($possiblePrefixes as $possiblePrefix) {
                if (method_exists($model, $possiblePrefix . $fieldName)) {
                    $getterPrefix = $possiblePrefix;

                    break;
                }
            }

            if ($getterPrefix === null) {
                continue;
            }

            $value = $model->{$getterPrefix . $fieldName}();

            if ($value === null) {
                continue;
            }

            /** @var mysqlField $fieldObject */
            $fieldObject = $mysqlTable->{$field};

            if ($this->getColumnType($fieldObject->getType()) === self::TYPE_DATE_TIME) {
                $fieldObject->setValue($value->format('Y-m-d H:i:s'));
                $model->{'set' . $fieldName}($this->dateTimeService->get((string) $fieldObject->getValue()));
            } elseif (is_object($value) && enum_exists($value::class)) {
                $fieldObject->setValue($value->value);
            } elseif (is_array($value)) {
                $fieldObject->setValue($this->jsonUtility->encode($value));
            } else {
                $fieldObject->setValue(is_bool($value) ? (int) $value : $value);
            }
        }

        return $mysqlTable;
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