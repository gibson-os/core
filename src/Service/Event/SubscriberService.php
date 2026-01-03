<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Event;

use GibsonOS\Core\Attribute\Event\Subscriber;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\EventSubscriber\SubscriberInterface;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\EventService;

class SubscriberService
{
    /**
     * @param SubscriberInterface[] $subscribers
     */
    public function __construct(
        private readonly ReflectionManager $reflectionManager,
        private readonly EventService $eventService,
        #[GetServices(['*/src/EventSubscriber'], SubscriberInterface::class)]
        private readonly array $subscribers,
    ) {
    }

    public function setSubscriberEvents(): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriberReflection = $this->reflectionManager->getReflectionClass($subscriber);

            foreach ($this->reflectionManager->getAttributes($subscriberReflection, Subscriber::class) as $eventSubscriberAttribute) {
                $className = $eventSubscriberAttribute->getClassName();
                $trigger = $eventSubscriberAttribute->getTrigger();
                $this->eventService->add(
                    $className,
                    $trigger,
                    fn (array $parameters) => $subscriber->event($className, $trigger, $parameters),
                );
            }
        }
    }
}
