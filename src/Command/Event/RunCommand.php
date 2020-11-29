<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use Psr\Log\LoggerInterface;

class RunCommand extends AbstractCommand
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventService
     */
    private $eventService;

    public function __construct(EventRepository $eventRepository, EventService $eventService, LoggerInterface $logger)
    {
        $this->eventRepository = $eventRepository;
        $this->eventService = $eventService;

        parent::__construct($logger);

        $this->setArgument('eventId', true);
    }

    /**
     * @throws ArgumentError
     * @throws DateTimeError
     * @throws SelectError
     */
    protected function run(): int
    {
        $this->eventService->runEvent(
            $this->eventRepository->getById((int) $this->getArgument('eventId')),
            false
        );

        return 0;
    }
}
