<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Model\User;
use GibsonOS\Core\Wrapper\ModelWrapper;
use OutOfBoundsException;

class SessionService
{
    private const LOGIN = 'login';

    private const USER = 'user';

    private array $data;

    public function __construct(protected readonly ModelWrapper $modelWrapper)
    {
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
        return $this
            ->set(self::LOGIN, true)
            ->set(self::USER, $user)
            // @todo old stuff. Entfernen wenn alles umgebaut ist
            ->set('user_id', $user->getId())
            ->set('user_name', $user->getUser())
        ;
    }

    public function logout(): SessionService
    {
        return $this
            ->unset(self::LOGIN)
            ->unset(self::USER)
            // @todo old stuff. Entfernen wenn alles umgebaut ist
            ->unset('user_id')
            ->unset('user_name')
        ;
    }

    public function isLogin(): bool
    {
        return (bool) $this->getWithDefault(self::LOGIN, false);
    }

    public function getUser(): ?User
    {
        return $this->getWithDefault(self::USER);
    }

    public function getUserId(): ?int
    {
        /** @var User $user */
        $user = $this->getWithDefault(self::USER, (new User($this->modelWrapper))->setId(0));

        return $user->getId();
    }
}
