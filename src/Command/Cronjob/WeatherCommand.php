<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use DateTimeZone;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\WeatherService;
use JsonException;
use Psr\Log\LoggerInterface;

class WeatherCommand extends AbstractCommand
{
    private WeatherService $weatherService;

    private LocationRepository $locationRepository;

    private WeatherRepository $weatherRepository;

    private DateTimeService $dateTimeService;

    private LockService $lockService;

    public function __construct(
        LoggerInterface $logger,
        WeatherService $weatherService,
        LocationRepository $locationRepository,
        WeatherRepository $weatherRepository,
        DateTimeService $dateTimeService,
        LockService $lockService
    ) {
        parent::__construct($logger);
        $this->weatherService = $weatherService;
        $this->locationRepository = $locationRepository;
        $this->weatherRepository = $weatherRepository;
        $this->dateTimeService = $dateTimeService;
        $this->lockService = $lockService;
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     */
    protected function run(): int
    {
        $this->lockService->lock();

        foreach ($this->locationRepository->getToUpdate() as $location) {
            $lastRun = $this->dateTimeService->get();
            /** @var DateTime $oldLastRun */
            $oldLastRun = $location->getLastRun();

            if ($oldLastRun !== null) {
                $localTimezone = new DateTimeZone($location->getTimezone());
                $oldLastRun->setTimezone($localTimezone);
                $lastRunLocalTime = clone $lastRun;
                $lastRunLocalTime->setTimezone($localTimezone);
                $this->weatherRepository->deleteBetweenDates($location, $oldLastRun, $lastRunLocalTime);
            }

            $this->weatherService->load($location);
            $location
                ->setLastRun($lastRun)
                ->save()
            ;
        }

        $this->lockService->unlock();

        return 1;
    }
}
