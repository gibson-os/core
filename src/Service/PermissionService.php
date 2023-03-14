<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionViewRepository;

class PermissionService
{
    public function __construct(private readonly PermissionViewRepository $permissionViewRepository)
    {
    }

    /**
     * @throws SelectError
     */
    public function getPermission(string $module, string $task = null, string $action = null, int $userId = null): int
    {
        if ($task === null) {
            return $this->permissionViewRepository->getPermissionByModule($module, $userId)->getPermission();
        }

        if ($action === null) {
            return $this->permissionViewRepository->getPermissionByTask($module, $task, $userId)->getPermission();
        }

        return $this->permissionViewRepository->getPermissionByAction($module, $task, $action, $userId)->getPermission();
    }

    public function hasPermission(
        int $permission,
        string $module,
        string $task = null,
        string $action = null,
        int $userId = null
    ): bool {
        try {
            $permissionValue = $this->getPermission($module, $task, $action, $userId);
        } catch (SelectError) {
            return false;
        }

        return $this->checkPermission($permission, $permissionValue);
    }

    public function checkPermission(int $requiredPermission, int $permission): bool
    {
        if (
            $requiredPermission !== Permission::DENIED &&
            ($permission & Permission::DENIED)
        ) {
            return false;
        }

        return ($permission & $requiredPermission) === $requiredPermission;
    }

    public function isDenied(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(Permission::DENIED, $module, $task, $action, $userId);
    }

    public function hasReadPermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(Permission::READ, $module, $task, $action, $userId);
    }

    public function hasWritePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(Permission::WRITE, $module, $task, $action, $userId);
    }

    public function hasDeletePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(Permission::DELETE, $module, $task, $action, $userId);
    }

    public function hasManagePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(Permission::MANAGE, $module, $task, $action, $userId);
    }

    /**
     * @param array<string, array{permissionRequired: bool, items: array}> $requiredPermissions
     */
    public function getRequiredPermissions(array $requiredPermissions, ?int $userId): array
    {
        $permissions = [];

        foreach ($requiredPermissions as $moduleName => $requiredPermission) {
            $permissions[$moduleName] = $this->getRequiredPermissionItem($requiredPermission, $userId, $moduleName);
        }

        return $permissions;
    }

    /**
     * @param array{permissionRequired: bool, items: array} $permissionItem
     */
    private function getRequiredPermissionItem(
        array $permissionItem,
        ?int $userId,
        string $module,
        string $task = null,
        string $action = null,
    ): array {
        $permissions = [];

        if ($permissionItem['permissionRequired'] ?? false) {
            try {
                $permissions['permission'] = $this->getPermission($module, $task, $action, $userId);
            } catch (SelectError) {
                $permissions['permission'] = Permission::DENIED;
            }
        }

        if (isset($permissionItem['items'])) {
            $itemTask = $task;
            $itemAction = $action;
            $permissions['items'] = [];

            foreach ($permissionItem['items'] as $key => $item) {
                if ($task === null) {
                    $itemTask = $key;
                } elseif ($action === null) {
                    $itemAction = $key;
                }

                $permissions['items'][$key] = $this->getRequiredPermissionItem(
                    $item,
                    $userId,
                    $module,
                    $itemTask,
                    $itemAction
                );
            }
        }

        return $permissions;
    }
}
