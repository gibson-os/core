<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;

class PermissionService
{
    public function __construct(private PermissionRepository $permissionRepository)
    {
    }

    /**
     * @throws SelectError
     */
    public function getPermission(string $module, string $task = null, string $action = null, int $userId = null): int
    {
        if ($task === null) {
            return $this->permissionRepository->getPermissionByModule($module, $userId)->getPermission();
        }

        if ($action === null) {
            return $this->permissionRepository->getPermissionByTask($module, $task, $userId)->getPermission();
        }

        return $this->permissionRepository->getPermissionByAction($module, $task, $action, $userId)->getPermission();
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

        if (
            $permission !== Permission::DENIED &&
            ($permissionValue & Permission::DENIED)
        ) {
            return false;
        }

        return ($permissionValue & $permission) === $permission;
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
}
