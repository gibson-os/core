<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use DateTimeInterface;
use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\DateParameter;
use GibsonOS\Core\Dto\Parameter\IntParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Event\Describer\TimeDescriber;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\ServiceManagerService;

#[Event('Zeit')]
class TimeEvent extends AbstractEvent
{
    #[Event\Trigger('Zeitgesteuert')]
    public const TRIGGER_CRONJOB = 'cronjob';

    #[Event\Trigger('Sonnenaufgang')]
    public const TRIGGER_SUNSET = 'sunset';

    #[Event\Trigger('Sonnenuntergang')]
    public const TRIGGER_SUNRISE = 'sunrise';

    public function __construct(
        TimeDescriber $describer,
        ServiceManagerService $serviceManagerService,
        private DateTimeService $dateTimeService
    ) {
        parent::__construct($describer, $serviceManagerService);
    }

    /**
     * @param positive-int $seconds
     */
    #[Event\Method('Warten (s)')]
    public function sleep(
        #[Event\Parameter(IntParameter::class, 'Sekunden', ['range' => [1]])] int $seconds
    ): void {
        sleep($seconds);
    }

    /**
     * @param positive-int $microseconds
     */
    #[Event\Method('Warten (ms)')]
    public function usleep(
        #[Event\Parameter(IntParameter::class, 'Sekunden', ['range' => [1]])] int $microseconds
    ): void {
        usleep($microseconds);
    }

    #[Event\Method('Zwischen')]
    #[Event\ReturnValue(BoolParameter::class, 'Trifft zu')]
    public function between(
        #[Event\Parameter(DateParameter::class, 'Startdatum')] DateTimeInterface $start,
        #[Event\Parameter(DateParameter::class, 'Enddatum')] DateTimeInterface $end
    ): bool {
        $now = $this->dateTimeService->get();

        return $now >= $start && $now <= $end;
    }

    #[Event\Method('Jahr')]
    #[Event\ReturnValue(IntParameter::class, 'Jahr', ['range' => [0, 9999]])]
    public function year(): int
    {
        return (int) $this->dateTimeService->get()->format('Y');
    }

    #[Event\Method('Monat')]
    #[Event\ReturnValue(IntParameter::class, 'Monat', ['range' => [1, 12]])]
    public function month(): int
    {
        return (int) $this->dateTimeService->get()->format('n');
    }

    #[Event\Method('Tag')]
    #[Event\ReturnValue(IntParameter::class, 'Tag', ['range' => [0, 31]])]
    public function dayOfMonth(): int
    {
        return (int) $this->dateTimeService->get()->format('d');
    }

    #[Event\Method('Wochentag')]
    #[Event\ReturnValue(OptionParameter::class, 'Wochentag', ['options' => [[
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Sammstag',
        0 => 'Sonntag',
    ]]])]
    public function dayOfWeek(): int
    {
        return (int) $this->dateTimeService->get()->format('w');
    }

    #[Event\Method('Stunde')]
    #[Event\ReturnValue(IntParameter::class, 'Stunde', ['range' => [0, 23]])]
    public function hour(): int
    {
        return (int) $this->dateTimeService->get()->format('G');
    }

    #[Event\Method('Minute')]
    #[Event\ReturnValue(IntParameter::class, 'Minute', ['range' => [0, 59]])]
    public function minute(): int
    {
        return (int) $this->dateTimeService->get()->format('i');
    }

    #[Event\Method('Sekunde')]
    #[Event\ReturnValue(IntParameter::class, 'Sekunde', ['range' => [0, 59]])]
    public function second(): int
    {
        return (int) $this->dateTimeService->get()->format('s');
    }

    #[Event\Method('Ist Tag')]
    #[Event\ReturnValue(BoolParameter::class, 'Trifft zu')]
    public function isDay(): bool
    {
        $now = $this->dateTimeService->get();

        return
            $now >= $this->dateTimeService->get('@' . $this->dateTimeService->getSunrise($now)) &&
            $now < $this->dateTimeService->get('@' . $this->dateTimeService->getSunset($now))
        ;
    }

    #[Event\Method('Ist Nacht')]
    #[Event\ReturnValue(BoolParameter::class, 'Trifft zu')]
    public function isNight(): bool
    {
        return !$this->isDay();
    }
}
