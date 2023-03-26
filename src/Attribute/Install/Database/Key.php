<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Install\Database;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Key
{
    public function __construct(
        private bool $unique = false,
        private array $columns = [],
        private ?string $name = null,
    ) {
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): Key
    {
        $this->columns = $columns;

        return $this;
    }
}
