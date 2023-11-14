<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Dto\Model\Children;
use GibsonOS\Core\Dto\Model\PrimaryColumn;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Enum\Type;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Manager\TableManager;
use MDO\Query\DeleteQuery;
use MDO\Query\ReplaceQuery;
use MDO\Service\DeleteService;
use MDO\Service\ReplaceService;
use ReflectionAttribute;
use ReflectionException;
use Throwable;

class ModelManager
{
    private const TYPE_INT = 'int';

    private const TYPE_FLOAT = 'float';

    private const TYPE_STRING = 'string';

    private const TYPE_DATE_TIME = 'dateTime';

    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    /**
     * @var array<class-string, PrimaryColumn[]>
     */
    private array $primaryColumns = [];

    public const COLUMN_TYPES = [
        Type::BIT->value => self::TYPE_INT,
        Type::TINYINT->value => self::TYPE_INT,
        Type::SMALLINT->value => self::TYPE_INT,
        Type::MEDIUMINT->value => self::TYPE_INT,
        Type::INT->value => self::TYPE_INT,
        Type::BIGINT->value => self::TYPE_INT,
        Type::YEAR->value => self::TYPE_DATE_TIME,
        Type::TIME->value => self::TYPE_DATE_TIME,
        Type::DATE->value => self::TYPE_DATE_TIME,
        Type::DATETIME->value => self::TYPE_DATE_TIME,
        Type::TIMESTAMP->value => self::TYPE_DATE_TIME,
        Type::CHAR->value => self::TYPE_STRING,
        Type::VARCHAR->value => self::TYPE_STRING,
        Type::ENUM->value => self::TYPE_STRING,
        Type::SET->value => self::TYPE_STRING,
        Type::TINYTEXT->value => self::TYPE_STRING,
        Type::TEXT->value => self::TYPE_STRING,
        Type::MEDIUMTEXT->value => self::TYPE_STRING,
        Type::LONGTEXT->value => self::TYPE_STRING,
        Type::BINARY->value => self::TYPE_STRING,
        Type::VARBINARY->value => self::TYPE_STRING,
        Type::JSON->value => self::TYPE_STRING,
        Type::TINYBLOB->value => self::TYPE_STRING,
        Type::BLOB->value => self::TYPE_STRING,
        Type::MEDIUMBLOB->value => self::TYPE_STRING,
        Type::LONGBLOB->value => self::TYPE_STRING,
        Type::FLOAT->value => self::TYPE_FLOAT,
        Type::DOUBLE->value => self::TYPE_FLOAT,
        Type::DECIMAL->value => self::TYPE_FLOAT,
    ];

    public function __construct(
        private readonly DateTimeService $dateTimeService,
        private readonly JsonUtility $jsonUtility,
        private readonly ReflectionManager $reflectionManager,
        private readonly TableNameAttribute $tableAttribute,
        private readonly TableManager $tableManager,
        private readonly ReplaceService $replaceService,
        private readonly DeleteService $deleteService,
        private readonly Client $client,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws SaveError
     * @throws ReflectionException
     * @throws JsonException
     * @throws RecordException
     */
    public function saveWithoutChildren(ModelInterface $model): void
    {
        $childrenList = $this->getChildrenList($model);

        try {
            $record = $this->setToRecord($model);
            $replaceQuery = new ReplaceQuery($this->tableManager->getTable($model->getTableName()), $record->getValues());
            $record = $this->replaceService->replaceAndLoadRecord($replaceQuery);
            $this->loadFromRecord($record, $model);
        } catch (ClientException $exception) {
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
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function save(ModelInterface $model): void
    {
        $newTransaction = false;

        if (!$this->client->isTransaction()) {
            $newTransaction = true;
            $this->client->startTransaction();
        }

        $childrenList = $this->getChildrenList($model);

        try {
            $this->saveWithoutChildren($model);
        } catch (SaveError $exception) {
            if ($newTransaction) {
                $this->client->commit();
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
                    $this->client->rollback();
                }

                throw $exception;
            }
        }

        if ($newTransaction) {
            $this->client->commit();
        }
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    public function delete(ModelInterface $model): void
    {
        try {
            $this->deleteService->deleteRecord(
                $this->tableManager->getTable($model->getTableName()),
                $this->setToRecord($model),
            );
        } catch (ClientException $exception) {
            $exception = new DeleteError(previous: $exception);
            $exception->setModel($model);

            throw $exception;
        }
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     */
    public function loadFromRecord(Record $record, ModelInterface $model, string $prefix = ''): void
    {
        $table = $this->tableManager->getTable($model->getTableName());

        foreach ($table->getFields() as $field) {
            $fieldName = $this->transformFieldName($field->getName());
            $setter = 'set' . $fieldName;

            if (!method_exists($model, $setter)) {
                continue;
            }

            $value = $record->get($prefix . $field->getName())->getValue();

            if ($value === null) {
                $model->$setter($value);
            } else {
                switch ($this->getColumnType($field->getType())) {
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
                            strtoupper((string) $value) === 'CURRENT_TIMESTAMP()' ? 'now' : (string) $value,
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
                                $value,
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
     * @throws ClientException
     */
    public function setToRecord(ModelInterface $model): Record
    {
        $values = [];
        $table = $this->tableManager->getTable($model->getTableName());

        foreach ($table->getFields() as $field) {
            $fieldName = $field->getName();
            $transformedFieldName = $this->transformFieldName($fieldName);
            $getterPrefix = null;

            foreach (self::POSSIBLE_PREFIXES as $possiblePrefix) {
                if (method_exists($model, $possiblePrefix . $transformedFieldName)) {
                    $getterPrefix = $possiblePrefix;

                    break;
                }
            }

            if ($getterPrefix === null) {
                continue;
            }

            $value = $model->{$getterPrefix . $transformedFieldName}();

            if ($value === null) {
                if (!$field->hasAutoIncrement()) {
                    $values[$fieldName] = new Value(null);
                }

                continue;
            }

            if ($this->getColumnType($field->getType()) === self::TYPE_DATE_TIME) {
                $values[$fieldName] = new Value($value->format('Y-m-d H:i:s'));
                $model->{'set' . $transformedFieldName}($this->dateTimeService->get((string) $values[$fieldName]->getValue()));
            } elseif (is_object($value) && enum_exists($value::class)) {
                $values[$fieldName] = new Value($value->name);
            } elseif (is_array($value)) {
                $values[$fieldName] = new Value($this->jsonUtility->encode($value));
            } else {
                $values[$fieldName] = new Value(is_bool($value) ? (int) $value : $value);
            }
        }

        return new Record($values);
    }

    private function transformFieldName(string $fieldName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }

    private function getColumnType(Type $type): string
    {
        return self::COLUMN_TYPES[$type->value];
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ClientException
     * @throws RecordException
     */
    private function saveChildren(Children $children): void
    {
        $constraintAttribute = $children->getConstraint();
        $parentModelClassName = $constraintAttribute->getParentModelClassName();

        if ($parentModelClassName === null) {
            throw new ReflectionException(
                'Property "parentModelClassName" of constraint attribute is not set!',
            );
        }

        $childrenModel = new $parentModelClassName($this->modelWrapper);
        $tableName = $childrenModel->getTableName();
        $where = $constraintAttribute->getWhere();
        $parameters = $constraintAttribute->getWhereParameters();
        $parameters[] = $children->getParentId();
        $table = $this->tableManager->getTable($tableName);
        $deleteQuery = (new DeleteQuery($table))
            ->addWhere(new Where(
                sprintf(
                    '%s`%s_id`=?',
                    $where === null ? '' : '(' . $where . ') AND ',
                    $constraintAttribute->getParentColumn(),
                ),
                $parameters,
            ))
        ;

        $primaryColumns = $this->getPrimaryColumns($parentModelClassName);

        foreach ($children->getModels() as $childrenModel) {
            $this->save($childrenModel);

            foreach ($primaryColumns as $primaryColumn) {
                $columnName = $primaryColumn->getColumn()->getName() ??
                    $this->tableAttribute->transformName($primaryColumn->getReflectionProperty()->getName())
                ;
                $getter = 'get' . ucfirst($this->transformFieldName(
                    $primaryColumn->getColumn()->getName() ??
                    $primaryColumn->getReflectionProperty()->getName(),
                ));
                $deleteQuery->addWhere(new Where(sprintf('`%s`!=?', $columnName), [$childrenModel->$getter()]));
            }
        }

        $this->client->execute($deleteQuery);
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
                ReflectionAttribute::IS_INSTANCEOF,
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
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if ($constraintAttribute === null) {
                continue;
            }

            $propertyName = $reflectionProperty->getName();
            $getter = 'get' . ucfirst($propertyName);

            if ($this->reflectionManager->getTypeName($reflectionProperty) !== 'array') {
                $setter = 'set' . ucfirst($propertyName);

                if ($model instanceof AbstractModel && !$model->isConstraintLoaded($propertyName)) {
                    continue;
                }

                $model->$setter($model->$getter());

                continue;
            }

            $childrenList[] = new Children(
                $reflectionProperty,
                $constraintAttribute,
                $model->$getter(),
                $model,
            );
        }

        return $childrenList;
    }
}
