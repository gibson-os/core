<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Trigger;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\DateTimeParameter;
use GibsonOS\Core\Dto\Parameter\IntParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Event\TimeEvent;

class TimeDescriber implements DescriberInterface
{
    public const TRIGGER_CRONJOB = 'cronjob';

    public const TRIGGER_SUNSET = 'sunset';

    public const TRIGGER_SUNRISE = 'sunrise';

    public function getTitle(): string
    {
        return 'Zeit';
    }

    /**
     * @return Trigger[]
     */
    public function getTriggers(): array
    {
        return [
            self::TRIGGER_CRONJOB => (new Trigger('Zeitgesteuert')),
            self::TRIGGER_SUNRISE => (new Trigger('Sonnenaufgang')),
            self::TRIGGER_SUNSET => (new Trigger('Sonnenuntergang')),
        ];
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return [
            'sleep' => (new Method('Warten (s)'))
                ->setParameters([
                    'seconds' => (new IntParameter('Sekunden'))
                        ->setRange(1),
                ]),
            'usleep' => (new Method('Warten (ms)'))
                ->setParameters([
                    'microseconds' => (new IntParameter('Mikrosekunden'))
                        ->setRange(1),
                ]),
            'between' => (new Method('Zwischen'))
                ->setParameters([
                    'start' => (new DateTimeParameter('Startdatum')),
                    'end' => (new DateTimeParameter('Enddatum')),
                ])
                ->setReturns([
                    'value' => new BoolParameter('Trifft zu'),
                ]),
            'year' => (new Method('Jahr'))
                ->setReturns([
                    'value' => (new IntParameter('Jahr'))
                        ->setRange(0, 9999),
                ]),
            'month' => (new Method('Monat'))
                ->setReturns([
                    'value' => (new IntParameter('Monat'))
                        ->setRange(1, 12),
                ]),
            'dayOfMonth' => (new Method('Tag'))
                ->setReturns([
                    'value' => (new IntParameter('Tag'))
                        ->setRange(1, 31),
                ]),
            'dayOfWeek' => (new Method('Wochentag'))
                ->setReturns([
                    'value' => (new OptionParameter('Wochentag', [
                        1 => 'Montag',
                        2 => 'Dienstag',
                        3 => 'Mittwoch',
                        4 => 'Donnerstag',
                        5 => 'Freitag',
                        6 => 'Sammstag',
                        0 => 'Sonntag',
                    ])),
                ]),
            'hour' => (new Method('Stunde'))
                ->setReturns([
                    'value' => (new IntParameter('Stunde'))
                        ->setRange(0, 23),
                ]),
            'minute' => (new Method('Minute'))
                ->setReturns([
                    'value' => (new IntParameter('Minute'))
                        ->setRange(0, 59),
                ]),
            'second' => (new Method('Sekunde'))
                ->setReturns([
                    'value' => (new IntParameter('Sekunde'))
                        ->setRange(0, 59),
                ]),
            'isDay' => (new Method('Ist Tag')),
            'isNight' => (new Method('Ist Nacht')),
        ];
    }

    public function getEventClassName(): string
    {
        return TimeEvent::class;
    }
}
