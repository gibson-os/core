<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Event;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Subscriber
{
    /**
     * @param class-string $className
     */
    public function __construct(
        private readonly string $className,
        private readonly string $trigger,
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }
}
