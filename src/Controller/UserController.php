<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\RedirectResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Service\UserService;
use GibsonOS\Core\Store\UserStore;
use GibsonOS\Core\Utility\StatusCode;

class UserController extends AbstractController
{
    public function __construct(
        RequestService $requestService,
        TwigService $twigService,
        SessionService $sessionService,
        private PermissionAttribute $permissionAttribute
    ) {
        parent::__construct($requestService, $twigService, $sessionService);
    }

    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function index(UserStore $userStore): AjaxResponse
    {
        return $this->returnSuccess($userStore->getList());
    }

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
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function settings(UserRepository $userRepository, int $id = null): AjaxResponse
    {
        $this->checkUserPermission($id, Permission::READ);

        if ($id === null) {
            $id = $this->sessionService->getUserId() ?? 0;
        }

        return $this->returnSuccess($userRepository->getById($id));
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
            'user' => $user->getUser(),
            'token' => $device->getToken(),
        ]);
    }

    #[CheckPermission(Permission::WRITE)]
    public function logout(UserService $userService): ResponseInterface
    {
        $userService->logout();

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    #[CheckPermission(Permission::READ)]
    public function sessionRefresh(): AjaxResponse
    {
        return $this->returnSuccess();
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SaveError
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
        $this->checkUserPermission($id, Permission::WRITE);

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
     * @throws SelectError
     * @throws DeleteError
     */
    #[CheckPermission(Permission::MANAGE + Permission::DELETE)]
    public function delete(UserRepository $userRepository, int $id): AjaxResponse
    {
        $userRepository->getById($id)->delete();

        return $this->returnSuccess();
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    private function checkUserPermission(?int $userId, int $permission): void
    {
        if ($userId !== $this->sessionService->getUserId()) {
            $this->permissionAttribute->evaluateAttribute(new CheckPermission($permission & Permission::MANAGE));

            return;
        }

        $this->permissionAttribute->evaluateAttribute(new CheckPermission($permission));
    }
}
