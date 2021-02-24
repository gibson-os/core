<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTimeZone;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WeatherError;
use GibsonOS\Core\Mapper\WeatherMapper;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class WeatherService extends AbstractService
{
    private WebService $webService;

    private EnvService $envService;

    private WeatherMapper $weatherMapper;

    private DateTimeService $dateTimeService;

    private EventService $eventService;

    private WeatherRepository $weatherRepository;

    public function __construct(
        WebService $webService,
        EnvService $envService,
        WeatherMapper $weatherMapper,
        DateTimeService $dateTimeService,
        EventService $eventService,
        WeatherRepository $weatherRepository
    ) {
        $this->webService = $webService;
        $this->envService = $envService;
        $this->weatherMapper = $weatherMapper;
        $this->dateTimeService = $dateTimeService;
        $this->eventService = $eventService;
        $this->weatherRepository = $weatherRepository;
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     * @throws SaveError
     * @throws WeatherError
     */
    public function load(Location $location): void
    {
        $this->eventService->fire('beforeLoad', ['location' => $location]);

        $response = $this->getByCoordinates($location->getLatitude(), $location->getLongitude());
        $data = JsonUtility::decode(fread($response->getBody(), $response->getLength()));

        if (
            abs($location->getLatitude() - $data['lat']) >= .0001 ||
            abs($location->getLongitude() - $data['lon']) >= .0001
        ) {
            throw new WeatherError(
                'Coordinates from location ' . $location->getName() .
                ' (lat: ' . $location->getLatitude() . ', lon: ' . $location->getLongitude() . ') not equal with response' .
                ' (lat: ' . $data['lat'] . ', lon: ' . $data['lon'] . ')'
            );
        }

        if ($data['timezone'] !== $location->getTimezone()) {
            throw new WeatherError(
                'Timezone ' . $location->getTimezone() . ' from location ' . $location->getName() .
                ' not equal with ' . $data['timezone']
            );
        }

        $now = $this->dateTimeService->get('now', new DateTimeZone($location->getTimezone()));
        $this->weatherMapper->mapFromArray($data['current'], $location)->save();

        foreach ($data['hourly'] as $hourly) {
            $hourlyWeather = $this->weatherMapper->mapFromArray($hourly, $location);

            try {
                $hourlyWeather->setId(
                    $this->weatherRepository->getByDate($location, $hourlyWeather->getDate())->getId()
                );
            } catch (SelectError $e) {
                // Do nothing
            }

            if ($hourlyWeather->getDate() > $now) {
                $hourlyWeather->save();
            }
        }

        $this->eventService->fire('afterLoad', ['location' => $location, 'data' => $data]);
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
