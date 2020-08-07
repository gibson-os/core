<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\LockRepository;
use GibsonOS\Core\Service\CronjobService;
use GibsonOS\Core\Service\LockService;

class RunCommand extends AbstractCommand
{
    private const FLOCK_NAME = 'cronjob';

    private const FLOCK_NAME_NEW = 'cronjobNew';

    /**
     * @var CronjobService
     */
    private $cronjobService;

    /**
     * @var LockService
     */
    private $lockService;

    /**
     * @var LockRepository
     */
    private $lockRepository;

    public function __construct(
        CronjobService $cronjobService,
        LockService $flockService,
        LockRepository $lockRepository
    ) {
        $this->cronjobService = $cronjobService;
        $this->lockService = $flockService;
        $this->lockRepository = $lockRepository;

        $this->setArgument('user', true);
    }

    /**
     * @throws ArgumentError
     * @throws UnlockError
     * @throws LockError
     * @throws SelectError
     */
    protected function run(): int
    {
        $user = $this->getArgument('user') ?? '';

        try {
            $this->lockService->unlock(self::FLOCK_NAME_NEW . $user);
        } catch (UnlockError $e) {
            // Lock not exist
        }

        $this->lockService->lock(self::FLOCK_NAME_NEW . $user);
        $this->lockService->waitUnlockToLock(self::FLOCK_NAME . $user);
        $pid = getmypid();

        while ($this->lockRepository->getByName(self::FLOCK_NAME_NEW . $user)->getPid() === $pid) {
            $startSecond = (int) (new DateTime())->format('s');
            $this->cronjobService->run($user);

            do {
                usleep(100000);
                $endSecond = (int) (new DateTime())->format('s');
            } while ($startSecond === $endSecond);
        }

        $this->lockService->unlock(self::FLOCK_NAME . $user);

        return 0;
    }
}
