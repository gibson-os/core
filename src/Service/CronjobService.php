<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Repository\CronjobRepository;
use GibsonOS\Core\Utility\JsonUtility;

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

    public function __construct(CronjobRepository $cronjobRepository, CommandService $commandService)
    {
        $this->cronjobRepository = $cronjobRepository;
        $this->commandService = $commandService;
    }

    public function run(string $user): void
    {
        $dateTime = new DateTime();

        foreach ($this->cronjobRepository->getRunnableByUser($dateTime, $user) as $cronjob) {
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
