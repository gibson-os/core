<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Service\EventService;
use Psr\Log\LoggerInterface;

class CronjobCommand extends AbstractCommand
{
    private EventService $eventService;

    public function __construct(EventService $eventService, LoggerInterface $logger)
    {
        $this->eventService = $eventService;

        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     */
    protected function run(): int
    {
        $this->eventService->fire(TimeDescriber::class, TimeDescriber::TRIGGER_CRONJOB);

        return 0;
    }
}
