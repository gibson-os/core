<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
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
use JsonException;
use ReflectionException;

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
        DeviceRepository $deviceRepository,
        int $userPermission,
        #[GetModel] User $user = null
    ): AjaxResponse {
        $this->checkUserPermission($user?->getId(), Permission::READ, $userPermission);

        if ($user === null) {
            $user = $this->sessionService->getUser();
        }

        if ($user === null) {
            return $this->returnFailure('User not found!');
        }

        $userJson = $user->jsonSerialize();
        $userJson['devices'] = $deviceRepository->findByUserId($user->getId() ?? 0);

        return $this->returnSuccess($userJson);
    }

    /**
     * @throws SaveError
     * @throws UserError
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
     * @throws JsonException
     * @throws PermissionDenied
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     * @throws UserError
     *
     * @todo Model mapping?
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
        #[GetModel] User $user,
        array $devices,
        int $userPermission
    ): AjaxResponse {
        $this->checkUserPermission($user->getId(), Permission::DELETE, $userPermission);

        $deviceRepository->deleteByIds($devices, $user->getId());

        return $this->returnSuccess();
    }

    /**
     * @throws JsonException
     * @throws SaveError
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::WRITE)]
    public function savePermission(
        ModelManager $modelManager,
        int $permission,
        string $module,
        string $task = '',
        string $action = '',
        #[GetModel] User $user = null
    ): AjaxResponse {
        $modelManager->save(
            (new Permission())
            ->setUserId($user?->getId())
            ->setPermission($permission)
            ->setModule($module)
            ->setTask($task)
            ->setAction($action)
        );

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission(Permission::MANAGE + Permission::DELETE)]
    public function delete(ModelManager $modelManager, #[GetModel] User $user): AjaxResponse
    {
        $modelManager->delete($user);

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
