<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Exception;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Lock\LockException;
use GibsonOS\Core\Exception\Lock\UnlockException;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Lock;
use GibsonOS\Core\Repository\LockRepository;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use ReflectionException;

class LockService
{
    public function __construct(
        private readonly LockRepository $lockRepository,
        private readonly ProcessService $processService,
        private readonly ModelManager $modelManager,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws LockException
     * @throws JsonException
     */
    public function lock(string $name = null): void
    {
        $name = $this->getName($name);

        if ($this->isLocked($name)) {
            throw new LockException('Lock exists!');
        }

        try {
            $this->modelManager->save(
                (new Lock($this->modelWrapper))
                    ->setName($name)
                    ->setPid(getmypid()),
            );
        } catch (SaveError|Exception) {
            throw new LockException('Can not save lock!');
        }
    }

    /**
     * @throws LockException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function forceLock(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if ($this->processService->pidExists($lock->getPid())) {
                if (!$this->processService->kill($lock->getPid())) {
                    throw new LockException(sprintf('Can not kill process %d!', $lock->getPid()));
                }

                $lock = (new Lock($this->modelWrapper))->setName($name);
            }
        } catch (SelectError) {
            $lock = (new Lock($this->modelWrapper))->setName($name);
        }

        try {
            $this->modelManager->save($lock->setPid(getmypid()));
        } catch (SaveError) {
            throw new LockException('Can not save lock!');
        }
    }

    /**
     * @throws UnlockException
     * @throws JsonException
     */
    public function unlock(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);
            $this->modelManager->delete($lock);
        } catch (SelectError|DeleteError) {
            throw new UnlockException();
        }
    }

    /**
     * @throws JsonException
     */
    public function isLocked(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if ($this->processService->pidExists($lock->getPid())) {
                return true;
            }

            try {
                $this->modelManager->delete($lock);
            } catch (DeleteError) {
            }

            return false;
        } catch (SelectError) {
            return false;
        }
    }

    /**
     * @throws DateTimeError
     * @throws JsonException
     */
    public function waitUnlockToLock(string $name = null): void
    {
        try {
            $this->lock($name);
        } catch (LockException) {
            usleep(10);
            $this->waitUnlockToLock($name);
        }
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    public function kill(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);
            $this->processService->kill($lock->getPid());
            $this->modelManager->delete($lock);
        } catch (SelectError) {
        }
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function stop(string $name = null): void
    {
        $name = $this->getName($name);

        try {
            $this->modelManager->save($this->lockRepository->getByName($name)->setStop(true));
        } catch (SelectError) {
        }
    }

    /**
     * @throws LockException
     */
    public function shouldStop(string $name = null): bool
    {
        $name = $this->getName($name);

        try {
            return $this->lockRepository->getByName($name)->shouldStop();
        } catch (SelectError) {
            throw new LockException('Can not save lock!');
        }
    }

    private function getName(string $name = null): string
    {
        if (null !== $name) {
            return $name;
        }

        $caller = debug_backtrace();

        return str_replace(DIRECTORY_SEPARATOR, '', $caller[1]['file'] ?? '');
    }
}
