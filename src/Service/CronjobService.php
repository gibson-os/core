<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Utility\JsonUtility;
use Psr\Log\LoggerInterface;

class CronjobService
{
    public function __construct(private CronjobRepository $cronjobRepository, private CommandService $commandService, private LoggerInterface $logger)
    {
    }

    public function add(
        string $command,
        string $user,
        array $years,
        array $days_of_months,
        array $days_of_week,
        array $hours,
        array $minutes,
        array $seconds,
        array $arguments = null,
        array $options = null
    ): void {
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     */
    public function run(string $user): void
    {
        $dateTime = new DateTime();

        foreach ($this->cronjobRepository->getRunnableByUser($dateTime, $user) as $cronjob) {
            $this->logger->info(sprintf('Run cronjob %s', $cronjob->getCommand()));

            $arguments = $cronjob->getArguments();
            $options = $cronjob->getOptions();
            $this->commandService->executeAsync(
                $cronjob->getCommand(),
                empty($arguments) ? [] : (array) JsonUtility::decode($arguments),
                empty($options) ? [] : (array) JsonUtility::decode($options)
            );

            $cronjob->setLastRun($dateTime);
            $cronjob->save();

            $dateTime = new DateTime();
        }
    }
}
