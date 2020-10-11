<?php declare(strict_types=1);

namespace GibsonOS\Core\Command\Event;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\EventService;

class SunChangeCommand extends AbstractCommand
{
    /**
     * @var DateTimeService
     */
    private $dateTimeService;

    /**
     * @var EventService
     */
    private $eventService;

    public function __construct(DateTimeService $dateTimeService, EventService $eventService)
    {
        $this->dateTimeService = $dateTimeService;
        $this->eventService = $eventService;
    }

    protected function run(): int
    {
        $dateTimeNow = $this->dateTimeService->get();
        $sunChangeDateTime = $this->dateTimeService->get();
        $sunChangeDateTime->setTimestamp($this->dateTimeService->getSunrise($dateTimeNow));

        if ($sunChangeDateTime->format('Y-m-d H:i') === $dateTimeNow->format('Y-m-d H:i')) {
            $this->eventService->fire(TimeDescriber::TRIGGER_SUNRISE);
        }

        $sunChangeDateTime->setTimestamp($this->dateTimeService->getSunset($dateTimeNow));

        if ($sunChangeDateTime->format('Y-m-d H:i') === $dateTimeNow->format('Y-m-d H:i')) {
            $this->eventService->fire(TimeDescriber::TRIGGER_SUNSET);
        }

        return 0;
    }
}
