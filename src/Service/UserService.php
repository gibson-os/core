<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use DateTimeImmutable;
use Exception;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class UserService
{
    private const int PASSWORD_MIN_LENGTH = 6;

    public function __construct(
        private readonly EnvService $envService,
        private readonly UserRepository $userRepository,
        private readonly DeviceRepository $deviceRepository,
        private readonly SessionService $sessionService,
        private readonly ModelManager $modelManager,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws UserError
     * @throws ViolationException
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     */
    public function login(string $username, string $password): User
    {
        try {
            $user = $this->userRepository->getByUsernameAndPassword($username, $this->hashPassword($password));
            $this->modelManager->saveWithoutChildren($user->setLastLogin(new DateTimeImmutable()));
            $this->sessionService->login($user);

            return $user;
        } catch (SelectError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws UserError
     * @throws ViolationException
     */
    public function deviceLogin(string $token): Device
    {
        try {
            $device = $this->deviceRepository->getByToken($token);
            $user = $device->getUser();
            $this->modelManager->saveWithoutChildren($user->setLastLogin(new DateTimeImmutable()));
            $this->sessionService->login($user);

            return $device;
        } catch (SelectError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws SaveError
     * @throws Exception
     */
    public function addDevice(User $user, string $model, ?string $fcmToken = null): Device
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

        $device = (new Device($this->modelWrapper))
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
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws UserError
     */
    public function save(
        User $user,
        string $password,
        string $passwordRepeat,
    ): User {
        if ($password !== '' || $passwordRepeat !== '') {
            if ($password !== $passwordRepeat) {
                throw new UserError('Password not equal');
            }

            if (mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
                throw new UserError('Password to short');
            }

            $user->setPassword(md5($this->hashPassword($password)));
        }

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
                $this->envService->getString('PASSWORD_HASH_SALT') . $password,
            );
        } catch (GetError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }
}
