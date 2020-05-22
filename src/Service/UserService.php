<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Repository\UserRepository;

class UserService
{
    /**
     * @var EnvService
     */
    private $envService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var SessionService
     */
    private $sessionService;

    public function __construct(EnvService $envService, UserRepository $userRepository, SessionService $sessionService)
    {
        $this->envService = $envService;
        $this->userRepository = $userRepository;
        $this->sessionService = $sessionService;
    }

    /**
     * @throws UserError
     */
    public function login(string $username, string $password): bool
    {
        try {
            $user = $this->userRepository->getByUsernameAndPassword($username, $this->hashPassword($password));
            $this->sessionService->login($user);

            return true;
        } catch (SelectError $e) {
            return false;
        } catch (DateTimeError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    public function logout(): void
    {
        $this->sessionService->logout();
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
