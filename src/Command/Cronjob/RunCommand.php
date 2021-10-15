<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\LockRepository;
use GibsonOS\Core\Service\CronjobService;
use GibsonOS\Core\Service\LockService;
use Psr\Log\LoggerInterface;

class RunCommand extends AbstractCommand
{
    private const FLOCK_NAME = 'cronjob';

    private const FLOCK_NAME_NEW = 'cronjobNew';

    public function __construct(
        private CronjobService $cronjobService,
        private LockService $lockService,
        private LockRepository $lockRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->setArgument('user', true);
    }

    /**
     * @throws ArgumentError
     * @throws LockError
     * @throws SelectError
     * @throws UnlockError
     * @throws DateTimeError
     * @throws SaveError
     */
    protected function run(): int
    {
        $user = $this->getArgument('user') ?? '';

        try {
            $this->lockService->unlock(self::FLOCK_NAME_NEW . $user);
        } catch (UnlockError) {
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
