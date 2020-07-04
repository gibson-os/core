<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\RedirectResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\UserService;
use GibsonOS\Core\Utility\StatusCode;

class UserController extends AbstractController
{
    /**
     * @throws UserError
     */
    public function login(
        UserService $userService,
        ?string $username,
        ?string $password
    ): RedirectResponse {
        if (empty($password) || empty($username)) {
            return new RedirectResponse($this->requestService->getBaseDir());
        }

        $userService->login($username, $password);

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    public function appLogin(
        UserService $userService,
        string $model,
        string $username,
        string $password
    ): AjaxResponse {
        if (empty($password) || empty($username)) {
            return $this->returnFailure('Login Error', StatusCode::UNAUTHORIZED);
        }

        $user = $userService->login($username, $password);
        $device = $userService->addDevice($user, $model);

        return $this->returnSuccess(['token' => $device->getToken()]);
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

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function sessionRefresh(): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess();
    }
}
