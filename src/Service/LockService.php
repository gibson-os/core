<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Exception;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Lock;
use GibsonOS\Core\Repository\LockRepository;

class LockService
{
    public function __construct(private LockRepository $lockRepository, private ProcessService $processService)
    {
    }

    /**
     * @throws LockError
     */
    public function lock(string $name = null): void
    {
        $name = $this->getName($name);

        if ($this->isLocked($name)) {
            throw new LockError('Lock exists!');
        }

        try {
            (new Lock())
                ->setName($name)
                ->setPid(getmypid())
                ->save()
            ;
        } catch (SaveError|Exception) {
            throw new LockError('Can not save lock!');
        }
    }

    /**
     * @throws LockError
     */
    public function forceLock(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if ($this->processService->pidExists($lock->getPid())) {
                if (!$this->processService->kill($lock->getPid())) {
                    throw new LockError(sprintf('Can not kill process %d!', $lock->getPid()));
                }

                $lock = (new Lock())->setName($name);
            }
        } catch (SelectError) {
            $lock = (new Lock())->setName($name);
        }

        try {
            $lock
                ->setPid(getmypid())
                ->save()
            ;
        } catch (SaveError) {
            throw new LockError('Can not save lock!');
        }
    }

    /**
     * @throws UnlockError
     */
    public function unlock(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);
            $lock->delete();
        } catch (SelectError|DeleteError) {
            throw new UnlockError();
        }
    }

    public function isLocked(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if ($this->processService->pidExists($lock->getPid())) {
                return true;
            }

            try {
                $lock->delete();
            } catch (DeleteError) {
                // do nothing
            }

            return false;
        } catch (SelectError) {
            return false;
        }
    }

    /**
     * @throws DateTimeError
     */
    public function waitUnlockToLock(string $name = null): void
    {
        try {
            $this->lock($name);
        } catch (LockError) {
            usleep(10);
            $this->waitUnlockToLock($name);
        }
    }

    /**
     * @throws DeleteError
     */
    public function kill(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);
            $this->processService->kill($lock->getPid());
            $lock->delete();
        } catch (SelectError) {
            // Do nothing
        }
    }

    /**
     * @throws SaveError
     */
    public function stop(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $this->lockRepository->getByName($name)
                ->setStop(true)
                ->save()
            ;
        } catch (SelectError) {
            // Do nothing
        }
    }

    public function shouldStop(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            return $this->lockRepository->getByName($name)->shouldStop();
        } catch (SelectError) {
            throw new LockError('Can not save lock!');
        }
    }

    private function getName(string $name = null): string
    {
        if (null !== $name) {
            return $name;
        }

        $caller = debug_backtrace();

        return str_replace(DIRECTORY_SEPARATOR, '', $caller[1]['file']);
    }
}
