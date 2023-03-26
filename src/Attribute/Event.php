<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Event
{
    public function __construct(private string $title)
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
