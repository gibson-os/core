<?php
declare(strict_types=1);

namespace GibsonOS\Core\EventSubscriber;

interface SubscriberInterface
{
    /**
     * @param class-string $className
     */
    public function event(string $className, string $trigger, array $parameters): void;
}
