<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\Permission;
use GibsonOS\Core\Model\User\Permission as UserPermission;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Role\PermissionStore;
use GibsonOS\Core\Store\Role\UserStore;
use GibsonOS\Core\Store\RoleStore;
use JsonException;
use ReflectionException;
use Traversable;

class RoleController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::READ)]
    public function getIndex(
        RoleStore $roleStore,
        int $start = 0,
        int $limit = 0,
        array $sort = [],
    ): AjaxResponse {
        $roleStore->setLimit($limit, $start);
        $roleStore->setSortByExt($sort);

        return $this->returnSuccess($roleStore->getList(), $roleStore->getCount());
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::WRITE)]
    public function postSave(
        ModelManager $modelManager,
        #[GetMappedModel] Role $role,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($role);

        return $this->returnSuccess($role);
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::DELETE)]
    public function deleteDelete(
        ModelManager $modelManager,
        #[GetModel] Role $role,
    ): AjaxResponse {
        $modelManager->delete($role);

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::WRITE)]
    public function postSavePermission(
        ModelManager $modelManager,
        #[GetMappedModel] Permission $permission,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($permission);

        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::READ)]
    public function getUsers(
        UserStore $userStore,
        #[GetModel] Role $role,
        int $start = 0,
        int $limit = 0,
        array $sort = [],
    ): AjaxResponse {
        $userStore
            ->setRole($role)
            ->setLimit($limit, $start)
            ->setSortByExt($sort)
        ;

        return $this->returnSuccess($userStore->getList(), $userStore->getCount());
    }

    /**
     * @throws SaveError
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::WRITE)]
    public function postSaveUser(
        #[GetMappedModel] Role\User $roleUser,
        ModelManager $modelManager,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($roleUser);

        return $this->returnSuccess($roleUser);
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::DELETE)]
    public function deleteDeleteUsers(
        #[GetModels(Role\User::class)] array $users,
        ModelManager $modelManager
    ): AjaxResponse {
        foreach ($users as $user) {
            $modelManager->delete($user);
        }

        return $this->returnSuccess();
    }

    #[CheckPermission(UserPermission::MANAGE + UserPermission::READ)]
    public function getPermissions(
        PermissionStore $permissionStore,
        PermissionRepository $permissionRepository,
        string $node
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
