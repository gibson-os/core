<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Exception;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;

class UserService
{
    private const PASSWORD_MIN_LENGTH = 6;

    public function __construct(private EnvService $envService, private UserRepository $userRepository, private DeviceRepository $deviceRepository, private SessionService $sessionService)
    {
    }

    /**
     * @throws UserError
     */
    public function login(string $username, string $password): User
    {
        try {
            $user = $this->userRepository->getByUsernameAndPassword($username, $this->hashPassword($password));
            $this->sessionService->login($user);

            return $user;
        } catch (SelectError | DateTimeError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws UserError
     */
    public function deviceLogin(string $token): User\Device
    {
        try {
            $device = $this->deviceRepository->getByToken($token);
            $this->sessionService->login($device->getUser());

            return $device;
        } catch (SelectError | DateTimeError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws Exception
     */
    public function addDevice(User $user, string $model): User\Device
    {
        // @todo remove after app release
        do {
            $id = (string) random_int(1, 9999999999999999);

            try {
                $this->deviceRepository->getById($id);
            } catch (SelectError) {
                break;
            }
        } while (true);

        $device = (new User\Device())
            ->setId($id) // @todo change to int value
            ->setUser($user)
            ->setModel($model)
            ->setToken(md5((string) mt_rand()) . md5((string) mt_rand()))
        ;
        $device->save();

        return $device;
    }

    public function logout(): void
    {
        $this->sessionService->logout();
    }

    /**
     * @throws DateTimeError
     * @throws SaveError
     * @throws UserError
     */
    public function save(
        User $user,
        string $username,
        string $password,
        string $passwordRepeat,
        ?string $host,
        ?string $ip
    ): User {
        if (empty($username)) {
            throw new UserError('Username is empty');
        }

        if (
            !empty($password) ||
            !empty($passwordRepeat)
        ) {
            if ($password != $passwordRepeat) {
                throw new UserError('Password not equal');
            }

            if (mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
                throw new UserError('Password to short');
            }

            $user->setPassword(md5($this->hashPassword($password)));
        }

        $user
            ->setUser($username)
            ->setHost($host)
            ->setIp($ip)
        ;
        $user->save();

        return $user;
    }

    /**
     * @throws UserError
     */
    private function hashPassword(string $password): string
    {
        try {
            return hash(
                $this->envService->getString('PASSWORD_HASH_ALGO'),
                $this->envService->getString('PASSWORD_HASH_SALT') . $password
            );
        } catch (GetError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }
}
