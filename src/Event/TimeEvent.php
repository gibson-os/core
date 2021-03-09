<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use DateTimeInterface;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\ServiceManagerService;

class TimeEvent extends AbstractEvent
{
    private DateTimeService $dateTimeService;

    public function __construct(
        TimeDescriber $describer,
        ServiceManagerService $serviceManagerService,
        DateTimeService $dateTimeService
    ) {
        parent::__construct($describer, $serviceManagerService);
        $this->dateTimeService = $dateTimeService;
    }

    public function sleep(int $seconds)
    {
        sleep($seconds);
    }

    public function usleep(int $microseconds)
    {
        usleep($microseconds);
    }

    public function between(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        $now = $this->dateTimeService->get();

        return $now >= $start && $now <= $end;
    }

    public function year(): int
    {
        return (int) $this->dateTimeService->get()->format('Y');
    }

    public function month(): int
    {
        return (int) $this->dateTimeService->get()->format('n');
    }

    public function dayOfMonth(): int
    {
        return (int) $this->dateTimeService->get()->format('d');
    }

    public function dayOfWeek(): int
    {
        return (int) $this->dateTimeService->get()->format('w');
    }

    public function hour(): int
    {
        return (int) $this->dateTimeService->get()->format('G');
    }

    public function minute(): int
    {
        return (int) $this->dateTimeService->get()->format('i');
    }

    public function second(): int
    {
        return (int) $this->dateTimeService->get()->format('s');
    }

    public function isDay(): bool
    {
        $now = $this->dateTimeService->get();

        return
            $now >= $this->dateTimeService->getSunrise($now) &&
            $now < $this->dateTimeService->getSunset($now)
        ;
    }

    public function isNight(): bool
    {
        return !$this->isDay();
    }
}
