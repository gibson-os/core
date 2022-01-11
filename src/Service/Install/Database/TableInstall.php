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
use GibsonOS\Core\Service\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
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

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        foreach ($this->getFiles($this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model') as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = new ReflectionClass($className);
            $tableAttributes = $reflectionClass->getAttributes(Table::class, ReflectionAttribute::IS_INSTANCEOF);

            if (count($tableAttributes) === 0) {
                continue;
            }

            $columnsAttributes = [];
            /** @var Table $tableAttribute */
            $tableAttribute = $tableAttributes[0]->newInstance();
            $tableAttribute->setName($tableAttribute->getName() ?? $this->transformName($reflectionClass->getName()));

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $columnAttributes = $reflectionProperty->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

                if (count($columnAttributes) === 0) {
                    continue;
                }

                /** @psalm-suppress UndefinedMethod */
                $type = $reflectionProperty->getType()?->getName() ?? '';
                /** @var Column $columnAttribute */
                $columnAttribute = $columnAttributes[0]->newInstance();
                $columnAttribute
                    ->setName($columnAttribute->getName() ?? $this->transformName($reflectionProperty->getName()))
                    ->setType($this->mapType($type))
                ;

                if ($type === 'bool') {
                    $columnAttribute
                        ->setLength(1)
                        ->addAttribute(Column::ATTRIBUTE_UNSIGNED)
                    ;
                }

                $columnsAttributes[] = $columnAttribute;
            }

            yield new Success('Todo install table');
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
    }
}
