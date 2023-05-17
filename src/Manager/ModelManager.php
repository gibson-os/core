<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use Exception;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Dto\Model\Children;
use GibsonOS\Core\Dto\Model\PrimaryColumn;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableAttribute;
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

    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    /**
     * @var array<class-string, PrimaryColumn[]>
     */
    private array $primaryColumns = [];

    public function __construct(
        private readonly mysqlDatabase $mysqlDatabase,
        private readonly DateTimeService $dateTimeService,
        private readonly JsonUtility $jsonUtility,
        private readonly ReflectionManager $reflectionManager,
        private readonly TableAttribute $tableAttribute
    ) {
    }

    /**
     * @throws SaveError
     * @throws ReflectionException
     */
    public function saveWithoutChildren(ModelInterface $model): void
    {
        $childrenList = $this->getChildrenList($model);

        try {
            $mysqlTable = $this->setToMysqlTable($model);
            $mysqlTable->save();
        } catch (Exception $exception) {
            $exception = new SaveError($exception->getMessage(), 0, $exception);
            $exception->setModel($model);

            throw $exception;
        }

        $mysqlTable->getReplacedRecord();

        try {
            $this->loadFromMysqlTable($mysqlTable, $model);
        } catch (JsonException|ReflectionException $exception) {
            $exception = new SaveError($exception->getMessage(), 0, $exception);
            $exception->setModel($model);

            throw $exception;
        }

        foreach ($childrenList as $children) {
            foreach ($children->getModels() as $childrenModel) {
                $setter = 'set' . ucfirst($children->getConstraint()->getParentColumn());
                $childrenModel->$setter($model);
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     */
    public function save(ModelInterface $model): void
    {
        $newTransaction = false;

        if (!$this->mysqlDatabase->isTransaction()) {
            $newTransaction = true;
            $this->mysqlDatabase->startTransaction();
        }

        $childrenList = $this->getChildrenList($model);

        try {
            $this->saveWithoutChildren($model);
        } catch (SaveError $exception) {
            if ($newTransaction) {
                $this->mysqlDatabase->commit();
            }

            throw $exception;
        }

        foreach ($childrenList as $children) {
            try {
                $this->saveChildren($children);
            } catch (SaveError|JsonException|ReflectionException $exception) {
                $exception = new SaveError($exception->getMessage(), 0, $exception);
                $exception->setModel($model);

                if ($newTransaction) {
                    $this->mysqlDatabase->rollback();
                }

                throw $exception;
            }
        }

        if ($newTransaction) {
            $this->mysqlDatabase->commit();
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
            $value = $fieldObject->getValue();

            if ($value === null) {
                $model->$setter($value);
            } else {
                switch ($this->getColumnType($fieldObject->getType())) {
                    case self::TYPE_INT:
                        try {
                            $model->$setter((int) $value);
                        } catch (Throwable) {
                            $model->$setter((bool) $value);
                        }

                        break;
                    case self::TYPE_FLOAT:
                        $model->$setter((float) $value);

                        break;
                    case self::TYPE_DATE_TIME:
                        $model->$setter($this->dateTimeService->get(
                            strtoupper((string) $value) === 'CURRENT_TIMESTAMP()' ? 'now' : (string) $value
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
                            $model->$setter($this->jsonUtility->decode((string) $value));

                            break;
                        }

                        if (enum_exists($typeName)) {
                            $model->$setter(constant(sprintf(
                                '%s::%s',
                                $typeName,
                                $fieldObject->getValue() ?? ''
                            )));

                            break;
                        }

                        $model->$setter($value);

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
            $getterPrefix = null;

            foreach (self::POSSIBLE_PREFIXES as $possiblePrefix) {
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
                $fieldObject->setValue($value->name);
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

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    private function saveChildren(Children $children): void
    {
        $constraintAttribute = $children->getConstraint();
        $parentModelClassName = $constraintAttribute->getParentModelClassName();

        if ($parentModelClassName === null) {
            throw new ReflectionException(
                'Property "parentModelClassName" of constraint attribute is not set!'
            );
        }

        $childrenModel = new $parentModelClassName($this->mysqlDatabase);
        $tableName = $childrenModel->getTableName();
        $where = $constraintAttribute->getWhere();
        $where = sprintf(
            '(%s`%s_id`=?)',
            $where === null ? '' : '(' . $where . ') AND ',
            $constraintAttribute->getParentColumn()
        );
        $mysqlTable = (new mysqlTable($this->mysqlDatabase, $tableName))
            ->setWhereParameters($constraintAttribute->getWhereParameters())
            ->addWhereParameter($children->getParentId())
        ;
        $primaryColumns = $this->getPrimaryColumns($parentModelClassName);
        $childrenWheres = [];

        foreach ($children->getModels() as $childrenModel) {
            $this->save($childrenModel);
            $primaryWheres = [];

            foreach ($primaryColumns as $primaryColumn) {
                $columnName = $primaryColumn->getColumn()->getName() ??
                    $this->tableAttribute->transformName($primaryColumn->getReflectionProperty()->getName())
                ;
                $primaryWheres[] = sprintf('`%s`!=?', $columnName);
                $getter = 'get' . ucfirst($this->transformFieldName(
                    $primaryColumn->getColumn()->getName() ??
                    $primaryColumn->getReflectionProperty()->getName()
                ));
                $mysqlTable->addWhereParameter($childrenModel->$getter());
            }

            $childrenWheres[] = sprintf('(%s)', implode(' AND ', $primaryWheres));
        }

        if (count($childrenWheres) > 0) {
            $where .= ' AND (' . implode(' AND ', $childrenWheres) . ')';
        }

        $mysqlTable
            ->setWhere($where)
            ->deletePrepared()
        ;
    }

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     *
     * @return PrimaryColumn[]
     */
    public function getPrimaryColumns(string $className): array
    {
        if (isset($this->primaryColumns[$className])) {
            return $this->primaryColumns[$className];
        }

        $reflectionClass = $this->reflectionManager->getReflectionClass($className);
        $primaryColumns = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $columnAttribute = $this->reflectionManager->getAttribute(
                $reflectionProperty,
                Column::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if ($columnAttribute === null) {
                continue;
            }

            if (!$columnAttribute->isPrimary()) {
                continue;
            }

            $primaryColumns[] = new PrimaryColumn($reflectionProperty, $columnAttribute);
        }

        $this->primaryColumns[$className] = $primaryColumns;

        return $primaryColumns;
    }

    /**
     * @throws ReflectionException
     *
     * @return Children[]
     */
    private function getChildrenList(ModelInterface $model): array
    {
        $childrenList = [];
        $reflectionClass = $this->reflectionManager->getReflectionClass($model);

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

            $childrenList[] = new Children(
                $reflectionProperty,
                $constraintAttribute,
                $model->$getter(),
                $model
            );
        }

        return $childrenList;
    }
}
