<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Trigger
{
    /**
     * @param array<array-key, array{key: string, className: class-string, title: ?string}> $parameters
     */
    public function __construct(private string $title, private array $parameters = [])
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
