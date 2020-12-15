<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Utility\JsonUtility;
use Psr\Log\LoggerInterface;

class CronjobService
{
    /**
     * @var CronjobRepository
     */
    private $cronjobRepository;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CronjobRepository $cronjobRepository,
        CommandService $commandService,
        LoggerInterface $logger
    ) {
        $this->cronjobRepository = $cronjobRepository;
        $this->commandService = $commandService;
        $this->logger = $logger;
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
