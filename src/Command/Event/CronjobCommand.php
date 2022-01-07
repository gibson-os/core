<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\TimeEvent;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Service\EventService;
use Psr\Log\LoggerInterface;

#[Cronjob]
class CronjobCommand extends AbstractCommand
{
    public function __construct(private EventService $eventService, LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     */
    protected function run(): int
    {
        $this->eventService->fire(TimeEvent::class, TimeEvent::TRIGGER_CRONJOB);

        return 0;
    }
}
