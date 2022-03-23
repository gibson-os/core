<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Exception;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;
use JsonException;
use ReflectionException;

class UserService
{
    private const PASSWORD_MIN_LENGTH = 6;

    public function __construct(
        private EnvService $envService,
        private UserRepository $userRepository,
        private DeviceRepository $deviceRepository,
        private SessionService $sessionService,
        private ModelManager $modelManager
    ) {
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
        } catch (SelectError $e) {
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
        } catch (SelectError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws SaveError
     * @throws Exception
     */
    public function addDevice(User $user, string $model, string $fcmToken = null): User\Device
    {
        // @todo remove after app release
        while (true) {
            $id = (string) random_int(1, 9999999999999999);

            try {
                $this->deviceRepository->getById($id);
            } catch (SelectError) {
                break;
            }
        }

        $device = (new User\Device())
            ->setId($id) // @todo change to int value
            ->setUser($user)
            ->setModel($model)
            ->setToken(md5((string) mt_rand()) . md5((string) mt_rand()))
            ->setFcmToken($fcmToken)
        ;
        $this->modelManager->save($device);

        return $device;
    }

    public function logout(): void
    {
        $this->sessionService->logout();
    }

    /**
     * @throws SaveError
     * @throws UserError
     * @throws JsonException
     * @throws ReflectionException
     */
    public function save(
        User $user,
        string $username,
        string $password,
        string $passwordRepeat,
        string $host = null,
        string $ip = null
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
        $this->modelManager->save($user);

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
