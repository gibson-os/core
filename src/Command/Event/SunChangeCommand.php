<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\TimeEvent;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\EventService;
use Override;
use Psr\Log\LoggerInterface;

/**
 * @description Run sun changed controlled events
 */
#[Cronjob(seconds: '0')]
class SunChangeCommand extends AbstractCommand
{
    public function __construct(
        private readonly DateTimeService $dateTimeService,
        private readonly EventService $eventService,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     */
    #[Override]
    protected function run(): int
    {
        $dateTimeNow = $this->dateTimeService->get();
        $sunChangeDateTime = $this->dateTimeService->get();
        $sunChangeDateTime->setTimestamp($this->dateTimeService->getSunrise($dateTimeNow));

        if ($sunChangeDateTime->format('Y-m-d H:i') === $dateTimeNow->format('Y-m-d H:i')) {
            $this->eventService->fire(TimeEvent::class, TimeEvent::TRIGGER_SUNRISE);
        }

        $sunChangeDateTime->setTimestamp($this->dateTimeService->getSunset($dateTimeNow));

        if ($sunChangeDateTime->format('Y-m-d H:i') === $dateTimeNow->format('Y-m-d H:i')) {
            $this->eventService->fire(TimeEvent::class, TimeEvent::TRIGGER_SUNSET);
        }

        return self::SUCCESS;
    }
}
