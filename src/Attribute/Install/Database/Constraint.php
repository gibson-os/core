<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Constraint
{
    /**
     * @param class-string $parentTable
     */
    public function __construct(
        private string $parentTable,
        private string $parentColumn,
        private ?string $onDelete = null,
        private ?string $onUpdate = null,
        private ?string $name = null,
    ) {
    }

    public function getParentTable(): string
    {
        return $this->parentTable;
    }

    public function getParentColumn(): string
    {
        return $this->parentColumn;
    }

    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
