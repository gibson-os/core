<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install\Database;

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
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use mysqlDatabase;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

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
        DirService $dirService,
        ServiceManagerService $serviceManagerService,
        EnvService $envService,
        LoggerInterface $logger,
        private mysqlDatabase $mysqlDatabase
    ) {
        parent::__construct($dirService, $serviceManagerService, $envService, $logger);
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
            $reflectionClass = new ReflectionClass($className);
            $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($tableAttributes) === 0) {
                continue;
            }

            $columnsAttributes = [];
            /** @var Table $tableAttribute */
            $tableAttribute = $tableAttributes[0]->newInstance();
            $tableName = $tableAttribute->getName() ?? $this->transformName(str_replace(
                DIRECTORY_SEPARATOR,
                '',
                str_replace($path . DIRECTORY_SEPARATOR, '', mb_substr($file, 0, -4))
            ));
            $tableAttribute->setName($tableName);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $columnAttributes = $reflectionProperty->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

                if (count($columnAttributes) === 0) {
                    continue;
                }

                /** @psalm-suppress UndefinedMethod */
                $type = $reflectionProperty->getType()?->getName() ?? '';
                /** @var Column $columnAttribute */
                $columnAttribute = $columnAttributes[0]->newInstance();
                $defaultValue = $reflectionProperty->getDefaultValue();
                $columnAttribute
                    ->setName($columnAttribute->getName() ?? $this->transformName($reflectionProperty->getName()))
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

                $columnsAttributes[] = $columnAttribute;
            }

            $tableExistsQuery =
                'SELECT `TABLE_NAME` ' .
                'FROM `information_schema`.`TABLES` ' .
                "WHERE `TABLE_NAME`='" . $tableName . "' " .
                "AND `TABLE_SCHEMA`='" . $this->envService->getString('MYSQL_DATABASE') . "'"
            ;

            if ($this->mysqlDatabase->sendQuery($tableExistsQuery) === false) {
                throw new InstallException(sprintf(
                    'Show table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error()
                ));
            }

            if ($this->mysqlDatabase->result?->fetch_row() === null) {
                $this->createTable($tableAttribute, $columnsAttributes);

                yield new Success(sprintf('Table "%s" installed!', $tableName));
            }
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

    private function transformName(string $name): string
    {
        $nameParts = explode('\\', $name);
        $name = lcfirst(end($nameParts));

        return mb_strtolower(preg_replace('/([A-Z])/', '_$1', $name));
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
     */
    private function createTable(Table $table, array $columns): void
    {
        $primaryColumns = array_filter(array_map(
            static fn (Column $column): ?string => $column->isPrimary() ? $column->getName() : null,
            $columns
        ));

        $query =
            'CREATE TABLE `' . $this->envService->getString('MYSQL_DATABASE') . '`.`' . $table->getName() . '` ' .
            '(' .
                implode(
                    ', ',
                    array_map(
                        fn (Column $column) => $this->getCreateColumn($column),
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

    private function getCreateColumn(Column $column): string
    {
        $type = $column->getType();
        $default = $column->getDefault();

        return
            '`' . $column->getName() . '` ' . $type . ' ' .
            (
                $type === Column::TYPE_ENUM
                    ? "('" . implode("', '", $column->getValues()) . "') "
                    : ($column->getLength() === null ? '' : '(' . $column->getLength() . ') ')
            ) .
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
//                    : 'DEFAULT ' . (
//                        $default === Column::DEFAULT_CURRENT_TIMESTAMP
//                            ? Column::DEFAULT_CURRENT_TIMESTAMP
//                            : ($default === null ? 'NULL' : "'" . $default . "'")
//                    )
            )
        ;
    }
}
