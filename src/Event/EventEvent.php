<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\EventParameter;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Event as EventModel;
use GibsonOS\Core\Service\EventService;

#[Event('Event')]
class EventEvent extends AbstractEvent
{
    public function __construct(
        EventService $eventService,
        ReflectionManager $reflectionManager,
        private readonly ModelManager $modelManager
    ) {
        parent::__construct($eventService, $reflectionManager);
    }

    /**
     * @throws \JsonException
     * @throws SaveError
     * @throws \ReflectionException
     */
    #[Event\Method('Aktivieren')]
    public function activate(#[Event\Parameter(EventParameter::class)] EventModel $event): void
    {
        $this->modelManager->saveWithoutChildren($event->setActive(true));
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws SaveError
     */
    #[Event\Method('Deaktivieren')]
    public function deactivate(#[Event\Parameter(EventParameter::class)] EventModel $event): void
    {
        $this->modelManager->saveWithoutChildren($event->setActive(false));
    }

    /**
     * @param EventModel $event
     * @param bool       $async
     *
     * @throws DateTimeError
     * @throws \JsonException
     * @throws SaveError
     * @throws EventException
     * @throws FactoryError
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
    public function isActive(#[Event\Parameter(EventParameter::class)] EventModel $event): bool
    {
        return $event->isActive();
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws SaveError
     */
    #[Event\Method('Stoppen')]
    public function stop(#[Event\Parameter(EventParameter::class)] EventModel $event): void
    {
        $this->eventService->stop($event);
    }
}
