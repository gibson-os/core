<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Run event
 */
class RunCommand extends AbstractCommand
{
    #[Argument('Event ID to execute')]
    private int $eventId;

    public function __construct(
        private EventRepository $eventRepository,
        private EventService $eventService,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     * @throws SaveError
     * @throws SelectError
     * @throws EventException
     * @throws FactoryError
     */
    protected function run(): int
    {
        $this->eventService->runEvent(
            $this->eventRepository->getById($this->eventId),
            false
        );

        return 0;
    }

    public function setEventId(int $eventId): RunCommand
    {
        $this->eventId = $eventId;

        return $this;
    }
}
