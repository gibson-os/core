<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use MDO\Client;
use MDO\Dto\Record;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionAttribute;
use ReflectionException;
use UnitEnum;

class TableInstall extends AbstractInstall implements PriorityInterface
{
    private const STRING_TYPES = [
        Column::TYPE_CHAR,
        Column::TYPE_VARCHAR,
        Column::TYPE_TINYTEXT,
        Column::TYPE_MEDIUMTEXT,
        Column::TYPE_TEXT,
        Column::TYPE_LONGTEXT,
        Column::TYPE_TINYBLOB,
        Column::TYPE_MEDIUMBLOB,
        Column::TYPE_BLOB,
        Column::TYPE_LONGBLOB,
        Column::TYPE_ENUM,
        Column::TYPE_SET,
    ];

    private const NO_DEFAULT_TYPES = [
        Column::TYPE_TINYTEXT,
        Column::TYPE_TEXT,
        Column::TYPE_MEDIUMTEXT,
        Column::TYPE_LONGTEXT,
        Column::TYPE_TINYBLOB,
        Column::TYPE_BLOB,
        Column::TYPE_MEDIUMBLOB,
        Column::TYPE_LONGBLOB,
    ];

    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly Client $client,
        private readonly TableNameAttribute $tableAttribute,
        private readonly ReflectionManager $reflectionManager,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws InstallException
     * @throws JsonException
     * @throws RecordException
     */
    public function install(string $module): Generator
    {
        $path = $this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model';

        foreach ($this->getFiles($path) as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);
            $tableAttribute = $this->reflectionManager->getAttribute($reflectionClass, Table::class);

            if ($tableAttribute === null) {
                continue;
            }

            $columnsAttributes = [];
            /** @var AbstractModel $model */
            $model = new $className($this->modelWrapper);
            $tableName = $model->getTableName();
            $tableAttribute->setName($tableName);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $columnAttribute = $this->reflectionManager->getAttribute(
                    $reflectionProperty,
                    Column::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                );

                if ($columnAttribute === null) {
                    continue;
                }

                $type = $this->reflectionManager->getTypeName($reflectionProperty)
                    ?? throw new ReflectionException(sprintf('No type found for "%s"', $reflectionProperty->getName()))
                ;
                $defaultValue = $reflectionProperty->getDefaultValue();
                $columnAttribute
                    ->setName($columnAttribute->getName() ?? $this->tableAttribute->transformName($reflectionProperty->getName()))
                    ->setType($columnAttribute->getType() ?? $this->mapType($type))
                    ->setNullable(
                        $columnAttribute->isAutoIncrement()
                            ? false
                            : ($columnAttribute->isNullable() === null
                                ? $this->reflectionManager->allowsNull($reflectionProperty)
                                : $columnAttribute->isNullable()),
                    )
                    ->setDefault(
                        $columnAttribute->getDefault() === null && ($reflectionProperty->hasDefaultValue())
                        ? (
                            is_bool($defaultValue)
                            ? (string) ((int) $defaultValue)
                            : (
                                $defaultValue === null
                                ? null
                                : (
                                    is_array($defaultValue)
                                    ? JsonUtility::encode($defaultValue)
                                    : (string) (
                                        is_object($defaultValue) && enum_exists($defaultValue::class)
                                        ? $defaultValue->name
                                        : $defaultValue
                                    )
                                )
                            )
                        )
                        : $columnAttribute->getDefault(),
                    )
                ;

                if ($type === 'bool') {
                    $columnAttribute
                        ->setLength(1)
                        ->addAttribute(Column::ATTRIBUTE_UNSIGNED)
                    ;
                }

                if (
                    $columnAttribute->getType() === Column::TYPE_ENUM
                    && count($columnAttribute->getValues()) === 0
                ) {
                    $type = $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty);
                    $this->reflectionManager->getReflectionEnum($type);
                    $columnAttribute->setValues(array_map(
                        fn (UnitEnum $enum): string => $enum->name,
                        $type::cases(),
                    ));
                }

                $columnsAttributes[$columnAttribute->getName() ?? ''] = $columnAttribute;
            }

            $tableExistsQuery =
                'SELECT `TABLE_NAME` ' .
                'FROM `information_schema`.`TABLES` ' .
                'WHERE `TABLE_NAME`=? ' .
                'AND `TABLE_SCHEMA`=?'
            ;
            $parameters = [$tableName, $this->envService->getString('MYSQL_DATABASE')];

            try {
                $result = $this->client->execute($tableExistsQuery, $parameters);
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Show table "%s" failed! Error: %s',
                    $tableName,
                    $this->client->getError(),
                ));
            }

            if (iterator_to_array($result->iterateRecords()) === []) {
                $this->createTable($tableAttribute, $columnsAttributes);

                yield new Success(sprintf('Table "%s" installed!', $tableName));

                continue;
            }

            try {
                $result = $this->client->execute(sprintf('SHOW FIELDS FROM `%s`', $tableName));
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Show field from table "%s" failed! Error: %s',
                    $tableName,
                    $this->client->getError(),
                ));
            }

            $updates = [];

            foreach ($result->iterateRecords() as $column) {
                $field = (string) $column->get('Field')->getValue();

                if (isset($columnsAttributes[$field])) {
                    $columnAttribute = $columnsAttributes[$field];

                    if ($this->isColumnModified($columnAttribute, $column)) {
                        $updates[] = 'MODIFY ' . $this->getColumnString($columnAttribute);
                    }

                    unset($columnsAttributes[$field]);

                    continue;
                }

                $updates[] = sprintf('DROP COLUMN `%s`', $field);
            }

            foreach ($columnsAttributes as $columnAttribute) {
                $updates[] = sprintf('ADD %s', $this->getColumnString($columnAttribute));
            }

            if ($updates === []) {
                continue;
            }

            $alterTableQuery = sprintf('ALTER TABLE `%s` %s', $tableName, implode(', ', $updates));
            $this->logger->debug($alterTableQuery);

            try {
                $this->client->execute($alterTableQuery);
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Modify table table "%s" failed! Error: %s',
                    $tableName,
                    $this->client->getError(),
                ));
            }

            yield new Success(sprintf('Table "%s" modified!', $tableName));
        }
    }

    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    public function getPriority(): int
    {
        return 700;
    }

    /**
     * @param class-string|string $type
     */
    private function mapType(string $type): string
    {
        return match ($type) {
            'int' => Column::TYPE_BIGINT,
            'float' => Column::TYPE_FLOAT,
            'bool' => Column::TYPE_TINYINT,
            'array' => Column::TYPE_JSON,
            'string' => Column::TYPE_VARCHAR,
            DateTimeInterface::class, DateTime::class, DateTimeImmutable::class => Column::TYPE_DATETIME,
            default => class_exists($type) && enum_exists($type) ? Column::TYPE_ENUM : Column::TYPE_VARCHAR,
        };
    }

    /**
     * @param Column[] $columns
     *
     * @throws InstallException
     */
    private function createTable(Table $table, array $columns): void
    {
        $primaryColumns = array_filter(array_map(
            static fn (Column $column): ?string => $column->isPrimary() ? $column->getName() : null,
            $columns,
        ));

        $query =
            'CREATE TABLE `' . $table->getName() . '` ' .
            '(' .
                implode(
                    ', ',
                    array_map(
                        fn (Column $column) => $this->getColumnString($column),
                        $columns,
                    ),
                ) .
                ($primaryColumns !== [] ? ', PRIMARY KEY (`' . implode('`, `', $primaryColumns) . '`)' : '') .
            ') ' .
            'ENGINE=' . $table->getEngine() . ' ' .
            'DEFAULT CHARSET ' . $table->getCharset()
        ;
        $this->logger->debug($query);

        try {
            $this->client->execute($query);
        } catch (ClientException) {
            throw new InstallException(sprintf(
                'Create table "%s" failed! Error: %s',
                $table->getName() ?? '',
                $this->client->getError(),
            ));
        }
    }

    private function getColumnType(Column $column): string
    {
        $type = $column->getType();

        return trim(
            $type .
            (
                $type === Column::TYPE_ENUM
                    ? "('" . implode("','", $column->getValues()) . "')"
                    : ($column->getLength() === null ? ' ' : '(' . $column->getLength() . ')')
            ),
        );
    }

    private function getColumnString(Column $column): string
    {
        $type = $column->getType();
        $default = $column->getDefault();

        return
            '`' . $column->getName() . '` ' . $this->getColumnType($column) . ' ' .
            implode(' ', $column->getAttributes()) . ' ' .
            (
                in_array($type, self::STRING_TYPES)
                    ? 'CHARACTER SET ' . $column->getCharset() . ' COLLATE ' . $column->getCollate() . ' '
                    : ''
            ) .
            ($column->isNullable() ? '' : 'NOT NULL ') .
            ($column->isAutoIncrement() ? 'AUTO_INCREMENT ' : '') .
            (
                in_array($type, self::NO_DEFAULT_TYPES) || ($default === null && $column->isNullable() === false)
                    ? ''
                    : 'DEFAULT ' . (
                        $default === Column::DEFAULT_CURRENT_TIMESTAMP
                            ? Column::DEFAULT_CURRENT_TIMESTAMP
                            : ($default === null ? 'NULL' : "'" . $default . "'")
                    )
            )
        ;
    }

    private function isColumnModified(Column $columnAttribute, Record $column): bool
    {
        if (($column->get('Null')->getValue() === 'YES') !== $columnAttribute->isNullable()) {
            return true;
        }

        $default = $columnAttribute->getDefault();

        if ($columnAttribute->getType() === Column::TYPE_JSON && $default !== null) {
            $default = "'" . $default . "'";
        }

        if (
            $column->get('Default')->getValue() !== ($default === Column::DEFAULT_CURRENT_TIMESTAMP ? 'current_timestamp()' : $default)
            && !($columnAttribute->getType() === Column::TYPE_TIMESTAMP && $default === null)
        ) {
            return true;
        }

        $columnType = $this->getColumnType($columnAttribute);
        $existingColumnType = (string) $column->get('Type')->getValue();

        if ($columnAttribute->getLength() === null) {
            $existingColumnType = preg_replace('/\(\d*\)/', '', $existingColumnType);

            if (!is_string($existingColumnType)) {
                return true;
            }
        }

        $extra = str_replace('()', '', (string) $column->get('Extra')->getValue());

        if ($columnAttribute->getType() !== Column::TYPE_TIMESTAMP) {
            $columnType = trim($columnType . ' ' . implode(' ', $columnAttribute->getAttributes()));
        } elseif (!is_string($extra) || mb_strtolower($extra) !== mb_strtolower(implode(' ', $columnAttribute->getAttributes()))) {
            return true;
        }

        $columnType = mb_strtolower($columnType);
        $existingColumnType = mb_strtolower($existingColumnType);

        if ($columnType !== $existingColumnType) {
            return !($columnType === 'json' && $existingColumnType === 'longtext');
        }

        return false;
    }
}
