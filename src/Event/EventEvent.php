<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\ServiceManagerService;

class EventEvent extends AbstractEvent
{
    private EventService $eventService;

    public function __construct(
        DescriberInterface $describer,
        ServiceManagerService $serviceManagerService,
        EventService $eventService
    ) {
        parent::__construct($describer, $serviceManagerService);
        $this->eventService = $eventService;
    }

    public function activate(Event $event): void
    {
        $event
            ->setActive(true)
            ->save()
        ;
    }

    public function deactivate(Event $event): void
    {
        $event
            ->setActive(false)
            ->save()
        ;
    }

    public function start(Event $event, bool $async): void
    {
        $this->eventService->runEvent($event, $async);
    }

    public function isActive(Event $event): bool
    {
        return $event->isActive();
    }
}
