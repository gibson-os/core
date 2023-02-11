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
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission as UserPermission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Role\UserStore;
use GibsonOS\Core\Store\RoleStore;

class RoleController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::READ)]
    public function index(
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
    public function save(
        ModelManager $modelManager,
        #[GetMappedModel] Role $role,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($role);

        return $this->returnSuccess($role);
    }

    /**
     * @throws DeleteError
     * @throws \JsonException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::DELETE)]
    public function delete(
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
    public function savePermission(
        ModelManager $modelManager,
        #[GetMappedModel] Permission $permission,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren($permission);

        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::READ)]
    public function users(
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
    public function addUser(
        #[GetModel] Role $role,
        #[GetModel(['id' => 'userId'])] User $user,
        ModelManager $modelManager,
    ): AjaxResponse {
        $modelManager->saveWithoutChildren(
            (new Role\User())
                ->setRole($role)
                ->setUser($user)
        );

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     * @throws \JsonException
     */
    #[CheckPermission(UserPermission::MANAGE + UserPermission::DELETE)]
    public function deleteUsers(
        #[GetModels(Role\User::class)] array $users,
        ModelManager $modelManager
    ): AjaxResponse {
        foreach ($users as $user) {
            $modelManager->delete($user);
        }

        return $this->returnSuccess();
    }
}
