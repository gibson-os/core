<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use OutOfBoundsException;
use ReflectionException;

class SessionService
{
    private const LOGIN = 'login';

    private const USER_ID = 'userId';

    private array $data;

    private ?User $user = null;

    public function __construct(
        protected readonly ModelWrapper $modelWrapper,
        protected readonly ModelManager $modelManager,
        protected readonly UserRepository $userRepository,
    ) {
        session_start();
        /**
         * @psalm-suppress InvalidScalarArgument
         * @psalm-suppress InvalidPropertyAssignmentValue
         */
        $this->data = $_SESSION;
        session_write_close();
    }

    public function set(string $key, mixed $value): SessionService
    {
        $this->data[$key] = $value;
        session_start();
        $_SESSION[$key] = $value;
        session_write_close();

        return $this;
    }

    public function get(string $key): mixed
    {
        if (!isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf('Session key $%s not exists!', $key));
        }

        return $this->data[$key];
    }

    public function unset(string $key): SessionService
    {
        if (!isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf('Session key $%s not exists!', $key));
        }

        unset($this->data[$key]);
        session_start();
        unset($_SESSION[$key]);
        session_write_close();

        return $this;
    }

    public function getWithDefault(string $key, mixed $default = null)
    {
        try {
            return $this->get($key);
        } catch (OutOfBoundsException) {
            return $default;
        }
    }

    public function login(User $user): SessionService
    {
        $this->user = $user;

        return $this
            ->set(self::LOGIN, true)
            ->set(self::USER_ID, $user->getId())
        ;
    }

    public function logout(): SessionService
    {
        $this->user = null;

        return $this
            ->unset(self::LOGIN)
            ->unset(self::USER_ID)
        ;
    }

    public function isLogin(): bool
    {
        return (bool) $this->getWithDefault(self::LOGIN, false);
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getUser(): ?User
    {
        $userId = $this->getUserId();

        if ($userId === 0) {
            return null;
        }

        if ($userId === $this->user?->getId()) {
            return $this->user;
        }

        try {
            $this->user = $this->userRepository->getById($userId);
        } catch (SelectError|ClientException) {
            return null;
        }

        return $this->user;
    }

    public function getUserId(): int
    {
        return $this->getWithDefault(self::USER_ID, 0);
    }
}
