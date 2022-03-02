<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Mapper;

class MapObjectParent
{
    public function __construct(private bool $default = true, private array $options = [])
    {
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
