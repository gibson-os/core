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
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use mysqlDatabase;
use ReflectionAttribute;
use ReflectionException;
use stdClass;

class TableInstall extends AbstractInstall implements PriorityInterface
{
    private const NUMBER_TYPES = [
        Column::TYPE_TINYINT,
        Column::TYPE_SMALLINT,
        Column::TYPE_MEDIUMINT,
        Column::TYPE_INT,
        Column::TYPE_BIGINT,
        Column::TYPE_DECIMAL,
        Column::TYPE_FLOAT,
        Column::TYPE_DOUBLE,
        Column::TYPE_BIT,
    ];

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

    private const DATE_TYPES = [
        Column::TYPE_DATE,
        Column::TYPE_DATETIME,
        Column::TYPE_TIMESTAMP,
        Column::TYPE_TIME,
        Column::TYPE_YEAR,
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
        private mysqlDatabase $mysqlDatabase,
        private TableAttribute $tableAttribute,
        private ReflectionManager $reflectionManager
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws InstallException
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
            /** @var ModelInterface $model */
            $model = new $className();
            $tableName = $model->getTableName();
            $tableAttribute->setName($tableName);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $columnAttribute = $this->reflectionManager->getAttribute(
                    $reflectionProperty,
                    Column::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );

                if ($columnAttribute === null) {
                    continue;
                }

                /** @psalm-suppress UndefinedMethod */
                $type = $reflectionProperty->getType()?->getName() ?? '';
                $defaultValue = $reflectionProperty->getDefaultValue();
                $columnAttribute
                    ->setName($columnAttribute->getName() ?? $this->tableAttribute->transformName($reflectionProperty->getName()))
                    ->setType($columnAttribute->getType() ?? $this->mapType($type))
                    ->setNullable(
                        $columnAttribute->isAutoIncrement()
                            ? false
                            : ($columnAttribute->isNullable() === null
                                ? ($reflectionProperty->getType()?->allowsNull() ?? false)
                                : $columnAttribute->isNullable())
                    )
                    ->setDefault(
                        $columnAttribute->getDefault() === null && ($reflectionProperty->hasDefaultValue())
                            ? (is_bool($defaultValue)
                                ? (string) ((int) $defaultValue)
                                : ($defaultValue === null ? null : (string) $defaultValue))
                            : $columnAttribute->getDefault()
                    )
                ;

                if ($type === 'bool') {
                    $columnAttribute
                        ->setLength(1)
                        ->addAttribute(Column::ATTRIBUTE_UNSIGNED)
                    ;
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

            if ($this->mysqlDatabase->execute($tableExistsQuery, $parameters) === false) {
                throw new InstallException(sprintf(
                    'Show table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error()
                ));
            }

            if ($this->mysqlDatabase->result?->fetch_row() === null) {
                $this->createTable($tableAttribute, $columnsAttributes);

                yield new Success(sprintf('Table "%s" installed!', $tableName));

                continue;
            }

            if ($this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `' . $tableName . '`') === false) {
                throw new InstallException(sprintf(
                    'Show field from table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error()
                ));
            }

            $updates = [];

            foreach ($this->mysqlDatabase->fetchObjectList() as $column) {
                if (isset($columnsAttributes[$column->Field])) {
                    $columnAttribute = $columnsAttributes[$column->Field];

                    if ($this->isColumnModified($columnAttribute, $column)) {
                        $updates[] = 'MODIFY ' . $this->getColumnString($columnAttribute);
                    }

                    unset($columnsAttributes[$column->Field]);

                    continue;
                }

                $updates[] = 'DROP COLUMN `' . $column->Field . '`';
            }

            foreach ($columnsAttributes as $columnAttribute) {
                $updates[] = 'ADD ' . $this->getColumnString($columnAttribute);
            }

            if (count($updates) === 0) {
                continue;
            }

            $alterTableQuery = 'ALTER TABLE `' . $tableName . '` ' . implode(', ', $updates);
            $this->logger->debug($alterTableQuery);

            if ($this->mysqlDatabase->sendQuery($alterTableQuery) === false) {
                throw new InstallException(sprintf(
                    'Modify table table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error()
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

    private function mapType(string $type): string
    {
        /** @psalm-suppress UndefinedMethod */
        return match ($type) {
            'int' => Column::TYPE_BIGINT,
            'float' => Column::TYPE_FLOAT,
            'bool' => Column::TYPE_TINYINT,
            'array' => Column::TYPE_JSON,
            'string' => Column::TYPE_VARCHAR,
            DateTimeInterface::class, DateTime::class, DateTimeImmutable::class => Column::TYPE_DATETIME,
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
            $columns
        ));

        $query =
            'CREATE TABLE `' . $table->getName() . '` ' .
            '(' .
                implode(
                    ', ',
                    array_map(
                        fn (Column $column) => $this->getColumnString($column),
                        $columns
                    )
                ) .
                (count($primaryColumns) > 0 ? ', PRIMARY KEY (`' . implode('`, `', $primaryColumns) . '`)' : '') .
            ') ' .
            'ENGINE=' . $table->getEngine() . ' ' .
            'DEFAULT CHARSET ' . $table->getCharset()
        ;
        $this->logger->debug($query);

        if ($this->mysqlDatabase->sendQuery($query) === false) {
            throw new InstallException(sprintf(
                'Create table "%s" failed! Error: %s',
                $table->getName() ?? '',
                $this->mysqlDatabase->error()
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
            )
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

    private function isColumnModified(Column $columnAttribute, stdClass $column): bool
    {
        if (($column->Null === 'YES') !== $columnAttribute->isNullable()) {
            return true;
        }

        $default = $columnAttribute->getDefault();

        if ($columnAttribute->getType() === Column::TYPE_JSON && $default !== null) {
            $default = "'" . $default . "'";
        }

        if (
            $column->Default !== ($default === Column::DEFAULT_CURRENT_TIMESTAMP ? 'current_timestamp()' : $default) &&
            !($columnAttribute->getType() === Column::TYPE_TIMESTAMP && $default === null)
        ) {
            return true;
        }

        $columnType = $this->getColumnType($columnAttribute);
        $existingColumnType = $column->Type;

        if ($columnAttribute->getLength() === null) {
            $existingColumnType = preg_replace('/\(\d*\)/', '', $existingColumnType);
        }

        if ($columnAttribute->getType() !== Column::TYPE_TIMESTAMP) {
            $columnType = trim($columnType . ' ' . implode(' ', $columnAttribute->getAttributes()));
        } elseif (mb_strtolower(str_replace('()', '', $column->Extra)) !== mb_strtolower(implode(' ', $columnAttribute->getAttributes()))) {
            return true;
        }

        $columnType = mb_strtolower($columnType);
        $existingColumnType = mb_strtolower($existingColumnType);

        if ($columnType !== $existingColumnType) {
            if ($columnType === 'json' && $existingColumnType === 'longtext') {
                return false;
            }

            return true;
        }

        return false;
    }
}
