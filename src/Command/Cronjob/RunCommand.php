<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command\Cronjob;

use DateTime;
use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\LockRepository;
use GibsonOS\Core\Service\CronjobService;
use GibsonOS\Core\Service\LockService;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Run cronjob
 */
class RunCommand extends AbstractCommand
{
    private const FLOCK_NAME = 'cronjob';

    private const FLOCK_NAME_NEW = 'cronjobNew';

    #[Argument('Run cronjobs for user')]
    private string $user;

    public function __construct(
        private CronjobService $cronjobService,
        private LockService $lockService,
        private LockRepository $lockRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws LockError
     * @throws SelectError
     * @throws UnlockError
     * @throws DateTimeError
     * @throws SaveError
     * @throws JsonException
     */
    protected function run(): int
    {
        try {
            $this->lockService->unlock(self::FLOCK_NAME_NEW . $this->user);
        } catch (UnlockError) {
            // Lock not exist
        }

        $this->lockService->lock(self::FLOCK_NAME_NEW . $this->user);
        $this->lockService->waitUnlockToLock(self::FLOCK_NAME . $this->user);
        $pid = getmypid();

        while ($this->lockRepository->getByName(self::FLOCK_NAME_NEW . $this->user)->getPid() === $pid) {
            $startSecond = (int) (new DateTime())->format('s');
            $this->cronjobService->run($this->user);

            do {
                usleep(100000);
                $endSecond = (int) (new DateTime())->format('s');
            } while ($startSecond === $endSecond);
        }

        $this->lockService->unlock(self::FLOCK_NAME . $this->user);

        return 0;
    }

    public function setUser(string $user): RunCommand
    {
        $this->user = $user;

        return $this;
    }
}
