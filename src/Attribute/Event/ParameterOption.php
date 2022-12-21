<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class ParameterOption
{
    public function __construct(
        private readonly string $parameterKey,
        private readonly string $optionKey,
        private readonly mixed $optionValue
    ) {
    }

    public function getParameterKey(): string
    {
        return $this->parameterKey;
    }

    public function getOptionKey(): string
    {
        return $this->optionKey;
    }

    public function getOptionValue(): mixed
    {
        return $this->optionValue;
    }
}
