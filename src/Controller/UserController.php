<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\RedirectResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\UserService;

class UserController extends AbstractController
{
    /**
     * @throws UserError
     */
    public function login(
        UserService $userService,
        ?string $username,
        ?string $password
    ): ResponseInterface {
        if (empty($password) || empty($username)) {
            return new RedirectResponse($this->requestService->getBaseDir());
        }

        $userService->login($username, $password);

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function logout(UserService $userService): ResponseInterface
    {
        $this->checkPermission(PermissionService::WRITE);

        $userService->logout();

        return new RedirectResponse($this->requestService->getBaseDir());
    }
}
