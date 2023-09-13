<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Repository\UserRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\RedirectResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\UserService;
use GibsonOS\Core\Store\User\PermissionStore;
use GibsonOS\Core\Store\UserStore;
use JsonException;
use ReflectionException;
use Traversable;

class UserController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function get(UserStore $userStore): AjaxResponse
    {
        return $this->returnSuccess($userStore->getList());
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws UserError
     */
    public function postLogin(
        UserService $userService,
        ?string $username,
        ?string $password,
    ): RedirectResponse {
        if (empty($password) || empty($username)) {
            return new RedirectResponse($this->requestService->getBaseDir());
        }

        $userService->login($username, $password);

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    /**
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ], ['id' => [Permission::READ, Permission::MANAGE]])]
    public function getSettings(
        DeviceRepository $deviceRepository,
        #[GetModel]
        User $user = null,
    ): AjaxResponse {
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
     * @throws ReflectionException
     */
    public function postAppLogin(
        UserService $userService,
        string $model,
        string $username,
        string $password,
        string $fcmToken,
    ): AjaxResponse {
        if (empty($password) || empty($username)) {
            return $this->returnFailure('Login Error', HttpStatusCode::UNAUTHORIZED);
        }

        $user = $userService->login($username, $password);
        $device = $userService->addDevice($user, $model, $fcmToken);

        return $this->returnSuccess([
            'id' => $user->getId(),
            'user' => $user->getUser(),
            'token' => $device->getToken(),
            'deviceId' => (int) $device->getId(),
        ]);
    }

    #[CheckPermission([Permission::WRITE])]
    public function getLogout(UserService $userService): ResponseInterface
    {
        $userService->logout();

        return new RedirectResponse($this->requestService->getBaseDir());
    }

    #[CheckPermission([Permission::READ])]
    public function getSessionRefresh(): AjaxResponse
    {
        return $this->returnSuccess();
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws UserError
     *
     * @todo Model mapping?
     */
    #[CheckPermission([Permission::WRITE], ['add' => [Permission::WRITE, Permission::MANAGE]])]
    public function post(
        UserService $userService,
        UserRepository $userRepository,
        #[GetMappedModel]
        User $user,
        string $password,
        string $passwordRepeat,
        bool $add = false,
    ): AjaxResponse {
        if ($user->getId() === null) {
            if (!$add) {
                $user = $this->sessionService->getUser()
                    ?? throw new UserError('User not found!')
                ;
            } else {
                try {
                    $userRepository->getByUsername($user->getUser());

                    return $this->returnFailure('Benutzername existiert schon.');
                } catch (SelectError) {
                    // Do nothing
                }
            }
        }

        return $this->returnSuccess($userService->save(
            $user,
            $password,
            $passwordRepeat,
        ));
    }

    #[CheckPermission([Permission::DELETE], ['id' => [Permission::DELETE, Permission::MANAGE]])]
    public function deleteDevice(
        DeviceRepository $deviceRepository,
        #[GetModel]
        User $user,
        array $devices,
    ): AjaxResponse {
        $deviceRepository->deleteByIds($devices, $user->getId());

        return $this->returnSuccess();
    }

    /**
     * @throws JsonException
     * @throws SaveError
     * @throws DeleteError
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::MANAGE, Permission::WRITE])]
    public function postPermission(
        ModelManager $modelManager,
        #[GetMappedModel]
        User\Permission $permission,
        #[GetModel]
        User\Permission $originalPermission = null,
    ): AjaxResponse {
        if ($permission->getPermission() === 0) {
            if ($originalPermission === null) {
                return $this->returnFailure('Permission not found!');
            }

            $modelManager->delete($originalPermission);

            return $this->returnSuccess();
        }

        $permission
            ->setTaskId($permission->getTaskId() === 0 ? null : $permission->getTaskId())
            ->setActionId($permission->getActionId() === 0 ? null : $permission->getActionId())
            ->setUserId($permission->getUserId() === 0 ? null : $permission->getUserId())
        ;
        $modelManager->saveWithoutChildren($permission);

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postUpdateFcmToken(
        ModelManager $modelManager,
        #[GetModel(['token' => 'token'])]
        Device $device,
        string $fcmToken,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($device->setFcmToken($fcmToken));

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::MANAGE, Permission::DELETE])]
    public function delete(ModelManager $modelManager, #[GetModel] User $user): AjaxResponse
    {
        $modelManager->delete($user);

        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     */
    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function getPermissions(
        PermissionStore $permissionStore,
        PermissionRepository $permissionRepository,
        string $node,
    ): AjaxResponse {
        $requiredPermissions = [];

        if (mb_strpos($node, 'a') === 0) {
            $actionId = (int) mb_substr($node, 1);
            $permissionStore->setActionId($actionId);

            foreach ($permissionRepository->findByActionId($actionId) as $permission) {
                $requiredPermissions[] = $permission->getPermission();
            }
        } elseif (mb_strpos($node, 't') === 0) {
            $permissionStore->setTaskId((int) mb_substr($node, 1));
        } else {
            $permissionStore->setModuleId((int) $node);
        }

        /** @var Traversable $permissions */
        $permissions = $permissionStore->getList();

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => iterator_to_array($permissions),
            'requiredPermissions' => $requiredPermissions,
        ]);
    }
}
