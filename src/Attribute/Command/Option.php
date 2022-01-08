<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Command;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Option
{
    public function __construct(private string $description = '')
    {
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
