<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\TimeEvent;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\EventService;
use Psr\Log\LoggerInterface;

/**
 * @description Run time controlled events
 */
#[Cronjob]
class CronjobCommand extends AbstractCommand
{
    public function __construct(private readonly EventService $eventService, LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     * @throws EventException
     * @throws FactoryError
     * @throws SaveError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    protected function run(): int
    {
        $this->eventService->fireInCommand(TimeEvent::class, TimeEvent::TRIGGER_CRONJOB);

        return self::SUCCESS;
    }
}
