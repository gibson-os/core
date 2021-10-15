<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use JsonException;
use Psr\Log\LoggerInterface;

class RunCommand extends AbstractCommand
{
    public function __construct(private EventRepository $eventRepository, private EventService $eventService, LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->setArgument('eventId', true);
    }

    /**
     * @throws ArgumentError
     * @throws DateTimeError
     * @throws SelectError
     * @throws SaveError
     * @throws JsonException
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
