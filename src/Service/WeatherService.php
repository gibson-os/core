<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTimeZone;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Event\WeatherEvent;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WeatherError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Mapper\WeatherMapper;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class WeatherService
{
    public function __construct(
        private readonly WebService $webService,
        private readonly EnvService $envService,
        private readonly WeatherMapper $weatherMapper,
        private readonly DateTimeService $dateTimeService,
        private readonly EventService $eventService,
        private readonly WeatherRepository $weatherRepository,
        private readonly ModelManager $modelManager,
    ) {
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     * @throws SaveError
     * @throws WeatherError
     */
    public function load(Location $location): void
    {
        $this->eventService->fire(
            WeatherEvent::class,
            WeatherEvent::TRIGGER_BEFORE_LOAD,
            ['location' => $location],
        );

        try {
            $response = $this->getByCoordinates($location->getLatitude(), $location->getLongitude());
        } catch (GetError|WebException $e) {
            throw new WeatherError($e->getMessage(), 0, $e);
        }

        $data = JsonUtility::decode($response->getBody()->getContent());

        if (
            abs($location->getLatitude() - $data['lat']) >= .0001
            || abs($location->getLongitude() - $data['lon']) >= .0001
        ) {
            throw new WeatherError(
                'Coordinates from location ' . $location->getName() .
                ' (lat: ' . $location->getLatitude() . ', lon: ' . $location->getLongitude() . ') not equal with response' .
                ' (lat: ' . $data['lat'] . ', lon: ' . $data['lon'] . ')',
            );
        }

        if ($data['timezone'] !== $location->getTimezone()) {
            throw new WeatherError(
                'Timezone ' . $location->getTimezone() . ' from location ' . $location->getName() .
                ' not equal with ' . $data['timezone'],
            );
        }

        $now = $this->dateTimeService->get('now', new DateTimeZone($location->getTimezone()));
        $current = $this->weatherMapper->mapFromArray($data['current'], $location);
        $this->modelManager->save($current);

        foreach ($data['hourly'] as $hourly) {
            $hourlyWeather = $this->weatherMapper->mapFromArray($hourly, $location);

            try {
                $hourlyWeather->setId(
                    $this->weatherRepository->getByDate($location, $hourlyWeather->getDate())->getId(),
                );
            } catch (SelectError) {
            }

            if ($hourlyWeather->getDate() > $now) {
                $this->modelManager->save($hourlyWeather);
            }
        }

        $eventParameters = $current->jsonSerialize();
        $eventParameters['location'] = $location;

        $this->eventService->fire(
            WeatherEvent::class,
            WeatherEvent::TRIGGER_AFTER_LOAD,
            $eventParameters,
        );
    }

    /**
     * @throws GetError
     * @throws WebException
     */
    private function getByCoordinates(float $latitude, float $longitude): Response
    {
        return $this->webService->get(
            new Request(
                'https://' . $this->envService->getString('OPENWEATHERMAP_URL') .
                'onecall?lat=' . $latitude . '&lon=' . $longitude .
                '&appid=' . $this->envService->getString('OPENWEATHERMAP_API_KEY') .
                '&units=metric&lang=de&exclude=minutely,daily',
            ),
        );
    }
}
