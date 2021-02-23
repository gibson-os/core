<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use DateTimeZone;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\DateTimeService;

class WeatherMapper
{
    private DateTimeService $dateTimeService;

    public function __construct(DateTimeService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    public function mapFromArray(array $data, Location $location, int $offset = 0): Weather
    {
        return (new Weather())
            ->setLocation($location)
            ->setDate($this->dateTimeService->get(
                '@' . ($data['dt'] + $offset),
                new DateTimeZone($location->getTimezone())
            ))
            ->setTemperature($data['temp'])
            ->setFeelsLike($data['feels_like'])
            ->setPressure($data['pressure'])
            ->setHumidity($data['humidity'])
            ->setDewPoint($data['dew_point'])
            ->setClouds($data['clouds'])
            ->setUvIndex($data['uvi'])
            ->setVisibility($data['visibility'])
            ->setWindSpeed($data['wind_speed'])
            ->setWindGust($data['wind_gust'] ?? null)
            ->setWindDegree($data['wind_deg'])
            ->setRain(isset($data['rain']) ? $data['rain']['1h'] : null)
            ->setSnow(isset($data['snow']) ? $data['snow']['1h'] : null)
            ->setDescription($data['weather'][0]['description'])
            ->setIcon($data['weather'][0]['icon'])
            ->setProbabilityOfPrecipitation($data['pop'] ?? null)
        ;
    }
}