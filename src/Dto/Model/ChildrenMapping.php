<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Model;

class ChildrenMapping
{
    /**
     * @param ChildrenMapping[] $children
     */
    public function __construct(
        private readonly string $propertyName,
        private readonly string $prefix,
        private readonly array $children = [],
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
