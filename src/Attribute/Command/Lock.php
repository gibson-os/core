<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Command;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Lock
{
    public function __construct(private readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
