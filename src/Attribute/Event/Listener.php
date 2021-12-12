<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Listener
{
    public function __construct(private string $forKey, private string $toKey, private array $options)
    {
    }

    public function getForKey(): string
    {
        return $this->forKey;
    }

    public function getToKey(): string
    {
        return $this->toKey;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
