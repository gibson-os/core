<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTimeZone;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Mapper\WeatherMapper;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class WeatherService extends AbstractService
{
    private WebService $webService;

    private EnvService $envService;

    private WeatherMapper $weatherMapper;

    private DateTimeService $dateTimeService;

    public function __construct(
        WebService $webService,
        EnvService $envService,
        WeatherMapper $weatherMapper,
        DateTimeService $dateTimeService
    ) {
        $this->webService = $webService;
        $this->envService = $envService;
        $this->weatherMapper = $weatherMapper;
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws JsonException
     */
    public function load(Location $location): void
    {
        $response = $this->getByCoordinates($location->getLatitude(), $location->getLongitude());
        $data = JsonUtility::decode(fread($response->getBody(), $response->getLength()));
        $now = $this->dateTimeService->get('now', new DateTimeZone($location->getTimezone()));
        $this->weatherMapper->mapFromArray($data['current'], $location, $data['timezone_offset'])->save();

        foreach ($data['hourly'] as $hourly) {
            $hourlyWeather = $this->weatherMapper->mapFromArray($hourly, $location, $data['timezone_offset']);

            if ($hourlyWeather->getDate() > $now) {
                $hourlyWeather->save();
            }
        }
    }

    private function getByCoordinates(float $latitude, float $longitude): Response
    {
        return $this->webService->get(
            (new Request(
                'https://' . $this->envService->getString('OPENWEATHERMAP_URL') .
                'onecall?lat=' . $latitude . '&lon=' . $longitude .
                '&appid=' . $this->envService->getString('OPENWEATHERMAP_API_KEY') .
                '&units=metric&lang=de&exclude=minutely,daily'
            ))->setPort(443)
        );
    }
}
