<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\UserRepository;
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

    /**
     * @throws UserError
     * @throws DateTimeError
     * @throws SaveError
     */
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

        return $this->returnSuccess([
            'id' => $user->getId(),
            'name' => $user->getUser(),
            'token' => $device->getToken(),
        ]);
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

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws UserError
     */
    public function save(
        UserService $userService,
        UserRepository $userRepository,
        string $username,
        string $password,
        string $passwordRepeat,
        string $host = null,
        string $ip = null,
        int $id = null
    ): AjaxResponse {
        $this->checkUserPermission($id, PermissionService::WRITE);

        $user = new User();

        if ($id !== null) {
            $user = $userRepository->getById($id);
        }

        return $this->returnSuccess($userService->save(
            $user,
            $username,
            $password,
            $passwordRepeat,
            $host,
            $ip
        ));
    }

    /**
     * @param $permission
     *
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    private function checkUserPermission(?int $userId, $permission): void
    {
        if ($userId !== $this->sessionService->getUserId()) {
            $this->checkPermission($permission & PermissionService::MANAGE);

            return;
        }

        $this->checkPermission($permission);
    }
}
