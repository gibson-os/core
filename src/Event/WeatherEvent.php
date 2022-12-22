<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Parameter\DateTimeParameter;
use GibsonOS\Core\Dto\Parameter\FloatParameter;
use GibsonOS\Core\Dto\Parameter\IntParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Dto\Parameter\Weather\LocationParameter;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\EventService;

#[Event('Wetter')]
class WeatherEvent extends AbstractEvent
{
    #[Event\Trigger('Vor dem laden', [
        ['key' => 'location', 'className' => LocationParameter::class],
    ])]
    public const TRIGGER_BEFORE_LOAD = 'beforeLoad';

    #[Event\Trigger('Nach dem laden', [
        ['key' => 'location', 'className' => LocationParameter::class],
        ['key' => 'id', 'className' => IntParameter::class, 'title' => 'ID'],
        ['key' => 'date', 'className' => DateTimeParameter::class, 'title' => 'Datum'],
        ['key' => 'temperature', 'className' => FloatParameter::class, 'title' => 'Temperature'],
        ['key' => 'feelsLike', 'className' => FloatParameter::class, 'title' => 'Gefühlte Temperature'],
        ['key' => 'pressure', 'className' => IntParameter::class, 'title' => 'Luftdruck'],
        ['key' => 'humidity', 'className' => IntParameter::class, 'title' => 'Luftfeuchtigkeit'],
        ['key' => 'dewPoint', 'className' => FloatParameter::class, 'title' => 'Taupunkt'],
        ['key' => 'clouds', 'className' => IntParameter::class, 'title' => 'Wolken'],
        ['key' => 'uvIndex', 'className' => IntParameter::class, 'title' => 'UV Index'],
        ['key' => 'windSpeed', 'className' => IntParameter::class, 'title' => 'Wind Geschwindigkeit'],
        ['key' => 'windDegree', 'className' => IntParameter::class, 'title' => 'Wind Richtung'],
        ['key' => 'visibility', 'className' => IntParameter::class, 'title' => 'Sichtweite'],
        ['key' => 'probabilityOfPrecipitation', 'className' => FloatParameter::class, 'title' => 'Regenwahrscheinlichkeit'],
        ['key' => 'description', 'className' => StringParameter::class, 'title' => 'Beschreibung'],
        ['key' => 'rain', 'className' => StringParameter::class, 'title' => 'Regen'],
        ['key' => 'snow', 'className' => StringParameter::class, 'title' => 'Schnee'],
        ['key' => 'windGust', 'className' => StringParameter::class, 'title' => 'Wind Böen'],
        ['key' => 'icon', 'className' => StringParameter::class, 'title' => 'Icon'],
    ])]
    public const TRIGGER_AFTER_LOAD = 'afterLoad';

    public function __construct(
        EventService $eventService,
        ReflectionManager $reflectionManager,
        private readonly WeatherRepository $weatherRepository
    ) {
        parent::__construct($eventService, $reflectionManager);
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Temperatur')]
    public function temperature(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getTemperature();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Gefühlte Temperatur')]
    public function feelsLike(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getFeelsLike();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Luftdruck')]
    public function pressure(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): int {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getPressure();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Luftfeuchtigkeit')]
    public function humidity(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): int {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getHumidity();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Taupunkt')]
    public function dewPoint(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getDewPoint();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Wolken')]
    public function clouds(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): int {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getClouds();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('UV Index')]
    public function uvIndex(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getUvIndex();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Wind Geschwindigkeit')]
    public function windSpeed(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindSpeed();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Wind Böen')]
    public function windGust(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): ?float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindGust();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Wind Richtung')]
    public function windDegree(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): int {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindDegree();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Sichtweite')]
    public function visibility(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): int {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getVisibility();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Regen')]
    public function rain(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): ?float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getRain();
    }

    /**
     * @throws SelectError
     */
    #[Event\Method('Schnee')]
    public function snow(
        #[Event\Parameter(LocationParameter::class)] Location $location,
        #[Event\Parameter(DateTimeParameter::class, 'Datum', ['increase' => [10]])] \DateTimeInterface $dateTime = null
    ): ?float {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getSnow();
    }
}
