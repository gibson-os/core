<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

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
    /**
     * @var LockRepository
     */
    private $lockRepository;

    public function __construct(LockRepository $lockRepository)
    {
        $this->lockRepository = $lockRepository;
    }

    /**
     * @param string|null $name
     *
     * @throws LockError
     */
    public function lock($name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);

            if (file_exists('/proc/' . $lock->getPid())) {
                throw new LockError();
            }
        } catch (SelectError $e) {
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
            throw new LockError();
        }
    }

    /**
     * @param string|null $name
     *
     * @throws UnlockError
     */
    public function unlock($name = null): void
    {
        $name = $this->getName($name);

        try {
            $lock = $this->lockRepository->getByName($name);
            $lock->delete();
        } catch (SelectError | DeleteError | DateTimeError $e) {
            throw new UnlockError();
        }
    }

    /**
     * @param string|null $name
     */
    public function waitUnlockToLock($name = null): void
    {
        try {
            $this->lock($name);
        } catch (LockError $e) {
            usleep(10);
            $this->waitUnlockToLock($name);
        }
    }

    private function getName(?string $name = null): string
    {
        if (null !== $name) {
            return $name;
        }

        $caller = debug_backtrace();

        return str_replace(DIRECTORY_SEPARATOR, '', $caller[1]['file']);
    }
}