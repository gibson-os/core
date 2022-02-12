<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\EventParameter;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Event as EventModel;
use GibsonOS\Core\Service\EventService;
use JsonException;

#[Event('Event')]
class EventEvent extends AbstractEvent
{
    public function __construct(EventService $eventService, ReflectionManager $reflectionManager)
    {
        parent::__construct($eventService, $reflectionManager);
    }

    /**
     * @throws SaveError
     */
    #[Event\Method('Aktivieren')]
    public function activate(
        #[Event\Parameter(EventParameter::class)] EventModel $event
    ): void {
        $event
            ->setActive(true)
            ->save()
        ;
    }

    /**
     * @throws SaveError
     */
    #[Event\Method('Deaktivieren')]
    public function deactivate(
        #[Event\Parameter(EventParameter::class)] EventModel $event
    ): void {
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
    #[Event\Method('Starten')]
    public function start(
        #[Event\Parameter(EventParameter::class)] EventModel $event,
        #[Event\Parameter(BoolParameter::class, 'Asynchron')] bool $async
    ): void {
        $this->eventService->runEvent($event, $async);
    }

    #[Event\Method('Ist aktiv')]
    #[Event\ReturnValue(BoolParameter::class, 'Aktiv')]
    public function isActive(
        #[Event\Parameter(EventParameter::class)] EventModel $event
    ): bool {
        return $event->isActive();
    }
}
