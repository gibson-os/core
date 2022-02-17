<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Service\PermissionService;
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
        private PermissionService $permissionService
    ) {
        parent::__construct($requestService, $twigService, $sessionService);
    }

    /**
     * @param UserStore $userStore
     *
     * @throws SelectError
     *
     * @return AjaxResponse
     */
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
     * @throws PermissionDenied
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function settings(
        UserRepository $userRepository,
        DeviceRepository $deviceRepository,
        int $userPermission,
        int $id = null
    ): AjaxResponse {
        $this->checkUserPermission($id, Permission::READ, $userPermission);

        if ($id === null) {
            $id = $this->sessionService->getUserId() ?? 0;
        }

        $user = $userRepository->getById($id)->jsonSerialize();
        $user['devices'] = $deviceRepository->findByUserId($id);

        return $this->returnSuccess($user);
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
     * @throws PermissionDenied
     * @throws SaveError
     * @throws SelectError
     * @throws UserError
     */
    public function save(
        UserService $userService,
        UserRepository $userRepository,
        int $userPermission,
        string $username,
        string $password,
        string $passwordRepeat,
        string $host = null,
        string $ip = null,
        int $id = null
    ): AjaxResponse {
        $this->checkUserPermission($id, Permission::WRITE, $userPermission);

        if (empty($username)) {
            return $this->returnFailure('Benutzername ist leer.');
        }

        $user = new User();

        if ($id !== null) {
            $user = $userRepository->getById($id);
        } else {
            try {
                $userRepository->getByUsername($username);

                return $this->returnFailure('Benutzername existiert schon.');
            } catch (SelectError) {
                // Do nothing
            }
        }

        if (
            !empty($password) ||
            !empty($passwordRepeat)
        ) {
            if ($password != $passwordRepeat) {
                return $this->returnFailure('Passwort stimmt nicht überein.');
            }

            if (
                !empty($password) &&
                mb_strlen($password) < 6
            ) {
                return $this->returnFailure('Passwort zu kurz. Mindestens 6 Zeichen.');
            }
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
     * @throws PermissionDenied
     */
    public function deleteDevice(
        DeviceRepository $deviceRepository,
        int $id,
        array $devices,
        int $userPermission
    ): AjaxResponse {
        $this->checkUserPermission($id, Permission::DELETE, $userPermission);

        $deviceRepository->deleteByIds($devices, $id);

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(Permission::MANAGE + Permission::WRITE)]
    public function savePermission(
        ?int $id,
        int $permission,
        string $module,
        string $task = '',
        string $action = ''
    ): AjaxResponse {
        (new Permission())
            ->setUserId($id ?: null)
            ->setPermission($permission)
            ->setModule($module)
            ->setTask($task)
            ->setAction($action)
            ->save()
        ;

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     */
    #[CheckPermission(Permission::MANAGE + Permission::DELETE)]
    public function delete(#[GetModel] User $user): AjaxResponse
    {
        $user->delete();

        return $this->returnSuccess();
    }

    /**
     * @throws PermissionDenied
     */
    private function checkUserPermission(?int $userId, int $requiredPermission, int $permission): void
    {
        if (
            $userId !== $this->sessionService->getUserId() &&
            !$this->permissionService->checkPermission($requiredPermission + Permission::MANAGE, $permission)
        ) {
            throw new PermissionDenied();
        }
    }
}
