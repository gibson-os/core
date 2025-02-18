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
        private readonly string $field,
        private readonly bool $multiple = true,
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

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->getOptions(),
            'multiple' => $this->isMultiple(),
        ];
    }
}
