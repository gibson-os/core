<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Model;

use MDO\Dto\Query\Where;

class ChildrenMapping
{
    /**
     * @param ChildrenMapping[] $children
     * @param Where[]           $wheres
     */
    public function __construct(
        private readonly string $propertyName,
        private readonly string $prefix,
        private readonly string $alias,
        private readonly array $children = [],
        private readonly array $wheres = [],
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

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getWheres(): array
    {
        return $this->wheres;
    }
}
