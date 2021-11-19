<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\ServiceManagerService;
use JsonException;

class EventEvent extends AbstractEvent
{
    public function __construct(
        DescriberInterface $describer,
        ServiceManagerService $serviceManagerService,
        private EventService $eventService
    ) {
        parent::__construct($describer, $serviceManagerService);
    }

    /**
     * @throws SaveError
     */
    public function activate(Event $event): void
    {
        $event
            ->setActive(true)
            ->save()
        ;
    }

    /**
     * @throws SaveError
     */
    public function deactivate(Event $event): void
    {
        $event
            ->setActive(false)
            ->save()
        ;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws JsonException
     */
    public function start(Event $event, bool $async): void
    {
        $this->eventService->runEvent($event, $async);
    }

    public function isActive(Event $event): bool
    {
        return $event->isActive();
    }
}
