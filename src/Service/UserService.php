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
            $this->sessionService->set('login', true);
            $this->sessionService->set('userId', $user->getId());

            // @todo old stuff. Entfernen wenn alles umgebaut ist
            $this->sessionService->set('user_id', $user->getId());
            $this->sessionService->set('user_name', $user->getUser());

            return true;
        } catch (SelectError $e) {
            return false;
        } catch (DateTimeError $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
    }

    public function logout(): void
    {
        $this->sessionService->unset('login');
        $this->sessionService->unset('userId');

        // @todo old stuff. Entfernen wenn alles umgebaut ist
        $this->sessionService->unset('user_id');
        $this->sessionService->unset('user_name');
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
