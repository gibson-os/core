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

class LockService extends AbstractService
{
    private LockRepository $lockRepository;

    private ProcessService $processService;

    public function __construct(LockRepository $lockRepository, ProcessService $processService)
    {
        $this->lockRepository = $lockRepository;
        $this->processService = $processService;
    }

    /**
     * @throws LockError
     */
    public function lock(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if ($this->processService->pidExists($lock->getPid())) {
                throw new LockError('Lock exists!');
            }
        } catch (SelectError | DateTimeError $e) {
            $lock = (new Lock())
                ->setName($name)
            ;
        }

        try {
            $lock
                ->setPid(getmypid())
                ->save()
            ;
        } catch (DateTimeError | SaveError | Exception $e) {
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

                $lock = (new Lock())
                    ->setName($name)
                ;
            }
        } catch (SelectError | DateTimeError $e) {
            $lock = (new Lock())
                ->setName($name)
            ;
        }

        try {
            $lock
                ->setPid(getmypid())
                ->save()
            ;
        } catch (DateTimeError | SaveError $e) {
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
        } catch (SelectError | DeleteError | DateTimeError $e) {
            throw new UnlockError();
        }
    }

    public function isLocked(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            $this->lockRepository->getByName($name);

            return true;
        } catch (SelectError | DateTimeError $e) {
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
        } catch (LockError $e) {
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
        } catch (SelectError | DateTimeError $e) {
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
        } catch (SelectError | DateTimeError $e) {
            // Do nothing
        }
    }

    public function shouldStop(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            return $this->lockRepository->getByName($name)->shouldStop();
        } catch (SelectError | DateTimeError $e) {
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
