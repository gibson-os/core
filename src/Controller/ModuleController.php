<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Service\ModuleService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\ActionStore;
use GibsonOS\Core\Store\ModuleStore;
use GibsonOS\Core\Store\SettingStore;
use GibsonOS\Core\Store\TaskStore;
use GibsonOS\Core\Store\User\PermissionStore;

class ModuleController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function index(
        ModuleStore $moduleStore,
        TaskStore $taskStore,
        ActionStore $actionStore,
        string $node = 'root'
    ): AjaxResponse {
        if ($node === 'root') {
            return $this->returnSuccess($moduleStore->getList());
        }

        if (mb_strpos($node, 't') === 0) {
            $actionStore->setTaskId((int) mb_substr($node, 1));

            return $this->returnSuccess($actionStore->getList());
        }

        $taskStore->setModuleId((int) $node);

        return $this->returnSuccess($taskStore->getList());
    }

    /**
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::WRITE)]
    public function scan(ModuleService $moduleService, ModuleStore $moduleStore): AjaxResponse
    {
        $moduleService->scan();

        return $this->returnSuccess($moduleStore->getList());
    }

    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function permission(
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

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => [...$permissionStore->getList()],
            'requiredPermissions' => $requiredPermissions,
        ]);
    }

    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function setting(SettingStore $settingStore, int $moduleId): AjaxResponse
    {
        $settingStore->setModuleId($moduleId);

        return $this->returnSuccess($settingStore->getList(), $settingStore->getCount());
    }
}
