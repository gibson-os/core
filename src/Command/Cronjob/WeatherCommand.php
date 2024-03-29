<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use DateTimeZone;
use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\WeatherError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\WeatherService;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * @description Collect weather information of required locations
 */
#[Cronjob(seconds: '40')]
#[Lock('weatherCommand')]
class WeatherCommand extends AbstractCommand
{
    public function __construct(
        LoggerInterface $logger,
        private readonly WeatherService $weatherService,
        private readonly LocationRepository $locationRepository,
        private readonly WeatherRepository $weatherRepository,
        private readonly DateTimeService $dateTimeService,
        private readonly ModelManager $modelManager,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     * @throws SaveError
     * @throws WeatherError
     * @throws ReflectionException
     */
    protected function run(): int
    {
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

            try {
                $this->weatherService->load($location);
            } catch (WeatherError|SaveError $e) {
                $this->modelManager->save(
                    $location
                        ->setError($e->getMessage())
                        ->setLastRun($lastRun),
                );

                throw $e;
            }

            $this->modelManager->save(
                $location
                    ->setError(null)
                    ->setLastRun($lastRun),
            );
        }

        return self::SUCCESS;
    }
}
