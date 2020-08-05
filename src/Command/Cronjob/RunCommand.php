<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\Flock\UnFlockError;
use GibsonOS\Core\Service\CronjobService;
use GibsonOS\Core\Service\FlockService;

class RunCommand extends AbstractCommand
{
    /**
     * @var CronjobService
     */
    private $cronjobService;

    /**
     * @var FlockService
     */
    private $flockService;

    public function __construct(
        CronjobService $cronjobService,
        FlockService $flockService
    ) {
        $this->cronjobService = $cronjobService;
        $this->flockService = $flockService;

        $this->setArgument('user', true);
    }

    /**
     * @throws ArgumentError
     * @throws UnFlockError
     */
    protected function run(): int
    {
        $pidFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gibsonOsCronjob.pid';
        $pid = getmypid();
        file_put_contents($pidFile, $pid);
        $this->flockService->waitUnFlockToFlock();

        do {
            $startSecond = (int) (new DateTime())->format('s');
            $this->cronjobService->run($this->getArgument('user') ?? '');

            do {
                usleep(100000);
                $endSecond = (int) (new DateTime())->format('s');
            } while ($startSecond === $endSecond);

            $actualPid = (int) file_get_contents($pidFile);
        } while ($actualPid === $pid);

        $this->flockService->unFlock();

        return 0;
    }
}
