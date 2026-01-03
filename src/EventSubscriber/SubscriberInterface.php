<?php
declare(strict_types=1);

namespace GibsonOS\Core\EventSubscriber;

interface SubscriberInterface
{
    public function event(array $parameters): void;
}
