<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\Permission as RolePermission;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Role\PermissionStore;
use GibsonOS\Core\Store\Role\UserStore;
use GibsonOS\Core\Store\RoleStore;
use JsonException;
use MDO\Exception\RecordException;
use ReflectionException;
use Traversable;

class RoleController extends AbstractController
{
    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function get(
        #[GetStore]
        RoleStore $roleStore,
    ): AjaxResponse {
        return $roleStore->getAjaxResponse();
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     */
    #[CheckPermission([Permission::MANAGE, Permission::WRITE])]
    public function post(
        ModelManager $modelManager,
        #[GetMappedModel]
        Role $role,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($role);

        return $this->returnSuccess($role);
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::MANAGE, Permission::DELETE])]
    public function delete(
        ModelManager $modelManager,
        #[GetModel]
        Role $role,
    ): AjaxResponse {
        $modelManager->delete($role);

        return $this->returnSuccess();
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     */
    #[CheckPermission([Permission::MANAGE, Permission::WRITE])]
    public function postPermission(
        ModelManager $modelManager,
        #[GetMappedModel]
        RolePermission $permission,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($permission);

        return $this->returnSuccess();
    }

    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function getUsers(
        #[GetStore]
        UserStore $userStore,
        #[GetModel]
        Role $role,
    ): AjaxResponse {
        $userStore->setRole($role);

        return $userStore->getAjaxResponse();
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ViolationException
     */
    #[CheckPermission([Permission::MANAGE, Permission::WRITE])]
    public function postUser(
        #[GetMappedModel]
        Role\User $roleUser,
        ModelManager $modelManager,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($roleUser);

        return $this->returnSuccess($roleUser);
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::MANAGE, Permission::DELETE])]
    public function deleteUsers(
        #[GetModels(Role\User::class)]
        array $users,
        ModelManager $modelManager,
    ): AjaxResponse {
        foreach ($users as $user) {
            $modelManager->delete($user);
        }

        return $this->returnSuccess();
    }

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

        /** @var Traversable $roles */
        $roles = $permissionStore->getList();

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => iterator_to_array($roles),
            'requiredPermissions' => $requiredPermissions,
        ]);
    }
}
