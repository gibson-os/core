<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\WeatherService;
use JsonException;
use Psr\Log\LoggerInterface;

class WeatherCommand extends AbstractCommand
{
    private WeatherService $weatherService;

    private LocationRepository $locationRepository;

    private WeatherRepository $weatherRepository;

    private DateTimeService $dateTimeService;

    public function __construct(
        LoggerInterface $logger,
        WeatherService $weatherService,
        LocationRepository $locationRepository,
        WeatherRepository $weatherRepository,
        DateTimeService $dateTimeService
    ) {
        parent::__construct($logger);
        $this->weatherService = $weatherService;
        $this->locationRepository = $locationRepository;
        $this->weatherRepository = $weatherRepository;
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     */
    protected function run(): int
    {
        foreach ($this->locationRepository->getToUpdate() as $location) {
            $lastRun = $this->dateTimeService->get();
            $oldLastRun = $location->getLastRun();

            if ($oldLastRun !== null) {
                $this->weatherRepository->deleteBetweenDates($location, $oldLastRun, $lastRun);
            }

            $this->weatherService->load($location);
            $location
                ->setLastRun($lastRun)
                ->save()
            ;
        }

        return 1;
    }
}
