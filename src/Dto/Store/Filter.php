<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Store;

use GibsonOS\Core\Dto\Store\Filter\Option;
use JsonSerializable;

class Filter implements JsonSerializable
{
    /**
     * @param Option[] $options
     */
    public function __construct(
        private readonly string $name,
        private readonly array $options,
        private readonly string $where,
        private readonly ?string $whereParameterName = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Option[]
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
        return [
            'name' => $this->getName(),
            'options' => $this->getOptions(),
        ];
    }
}
