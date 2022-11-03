<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Column
{
    public const DEFAULT_CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    public const ATTRIBUTE_UNSIGNED = 'UNSIGNED';

    public const ATTRIBUTE_ZEROFILL = 'ZEROFILL';

    public const ATTRIBUTE_BINARY = 'BINARY';

    public const ATTRIBUTE_CURRENT_TIMESTAMP = 'ON UPDATE CURRENT_TIMESTAMP';

    public const TYPE_TINYINT = 'tinyint';

    public const TYPE_SMALLINT = 'smallint';

    public const TYPE_MEDIUMINT = 'mediumint';

    public const TYPE_INT = 'int';

    public const TYPE_BIGINT = 'bigint';

    public const TYPE_DECIMAL = 'decimal';

    public const TYPE_FLOAT = 'float';

    public const TYPE_DOUBLE = 'double';

    public const TYPE_BIT = 'bit';

    public const TYPE_CHAR = 'char';

    public const TYPE_VARCHAR = 'varchar';

    public const TYPE_BINARY = 'binary';

    public const TYPE_VARBINARY = 'varbinary';

    public const TYPE_TINYTEXT = 'tinytext';

    public const TYPE_TEXT = 'text';

    public const TYPE_MEDIUMTEXT = 'mediumtext';

    public const TYPE_LONGTEXT = 'longtext';

    public const TYPE_JSON = 'json';

    public const TYPE_TINYBLOB = 'tinyblob';

    public const TYPE_BLOB = 'blob';

    public const TYPE_MEDIUMBLOB = 'mediumblob';

    public const TYPE_LONGBLOB = 'longblob';

    public const TYPE_ENUM = 'enum';

    public const TYPE_SET = 'set';

    public const TYPE_DATE = 'date';

    public const TYPE_DATETIME = 'datetime';

    public const TYPE_TIMESTAMP = 'timestamp';

    public const TYPE_TIME = 'time';

    public const TYPE_YEAR = 'year';

    public function __construct(
        private ?string $name = null,
        private ?string $type = null,
        private string $collate = 'utf8_general_ci',
        private string $charset = 'utf8',
        private ?string $default = null,
        private ?int $length = null,
        private array $attributes = [],
        private ?bool $nullable = null,
        private array $values = [],
        private bool $autoIncrement = false,
        private bool $primary = false,
    ) {
        $this->primary = $this->autoIncrement || $this->primary;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setName(string $name): Column
    {
        $this->name = $name;

        return $this;
    }

    public function setType(string $type): Column
    {
        $this->type = $type;

        return $this;
    }

    public function getCollate(): string
    {
        return $this->collate;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function setDefault(?string $default): Column
    {
        $this->default = $default;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): Column
    {
        $this->length = $length;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $attribute): Column
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function setNullable(?bool $nullable): Column
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): Column
    {
        $this->values = $values;

        return $this;
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }
}
