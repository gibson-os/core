<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Method
{
    public function __construct(private readonly string $title)
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
