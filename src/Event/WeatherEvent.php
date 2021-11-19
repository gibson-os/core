<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use DateTimeInterface;
use GibsonOS\Core\Event\Describer\WeatherDescriber;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\ServiceManagerService;

class WeatherEvent extends AbstractEvent
{
    public function __construct(
        WeatherDescriber $describer,
        ServiceManagerService $serviceManagerService,
        private WeatherRepository $weatherRepository
    ) {
        parent::__construct($describer, $serviceManagerService);
    }

    /**
     * @throws SelectError
     */
    public function temperature(Location $location, DateTimeInterface $dateTime = null): float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getTemperature();
    }

    /**
     * @throws SelectError
     */
    public function feelsLike(Location $location, DateTimeInterface $dateTime = null): float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getFeelsLike();
    }

    /**
     * @throws SelectError
     */
    public function pressure(Location $location, DateTimeInterface $dateTime = null): int
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getPressure();
    }

    /**
     * @throws SelectError
     */
    public function humidity(Location $location, DateTimeInterface $dateTime = null): int
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getHumidity();
    }

    /**
     * @throws SelectError
     */
    public function dewPoint(Location $location, DateTimeInterface $dateTime = null): float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getDewPoint();
    }

    /**
     * @throws SelectError
     */
    public function clouds(Location $location, DateTimeInterface $dateTime = null): int
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getClouds();
    }

    /**
     * @throws SelectError
     */
    public function uvIndex(Location $location, DateTimeInterface $dateTime = null): float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getUvIndex();
    }

    /**
     * @throws SelectError
     */
    public function windSpeed(Location $location, DateTimeInterface $dateTime = null): float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindSpeed();
    }

    /**
     * @throws SelectError
     */
    public function windGust(Location $location, DateTimeInterface $dateTime = null): ?float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindGust();
    }

    /**
     * @throws SelectError
     */
    public function windDegree(Location $location, DateTimeInterface $dateTime = null): int
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getWindDegree();
    }

    /**
     * @throws SelectError
     */
    public function visibility(Location $location, DateTimeInterface $dateTime = null): int
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getVisibility();
    }

    /**
     * @throws SelectError
     */
    public function rain(Location $location, DateTimeInterface $dateTime = null): ?float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getRain();
    }

    /**
     * @throws SelectError
     */
    public function snow(Location $location, DateTimeInterface $dateTime = null): ?float
    {
        return $this->weatherRepository->getByNearestDate($location, $dateTime)->getSnow();
    }
}
