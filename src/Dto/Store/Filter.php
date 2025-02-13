<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Store;

use JsonSerializable;

class Filter implements JsonSerializable
{
    /**
     * @param string[] $options
     */
    public function __construct(
        private readonly array $options,
        private readonly string $where,
        private readonly ?string $whereParameterName = null,
    ) {
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getWhere(): string
    {
        return $this->where;
    }

    public function getWhereParameterName(): ?string
    {
        return $this->whereParameterName;
    }

    public function jsonSerialize(): array
    {
        return $this->options;
    }
}
