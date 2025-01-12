<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;

class PermissionService
{
    public function __construct(private readonly PermissionViewRepository $permissionViewRepository)
    {
    }

    /**
     * @throws SelectError
     */
    public function getPermission(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): int {
        if ($task === null) {
            return $this->permissionViewRepository->getPermissionByModule($module, $userId)->getPermission();
        }

        if ($action === null || !$method instanceof HttpMethod) {
            return $this->permissionViewRepository->getPermissionByTask($module, $task, $userId)->getPermission();
        }

        return $this->permissionViewRepository->getPermissionByAction(
            $module,
            $task,
            $action,
            $method,
            $userId,
        )->getPermission();
    }

    public function hasPermission(
        int $permission,
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        try {
            $permissionValue = $this->getPermission($module, $task, $action, $method, $userId);
        } catch (SelectError) {
            return false;
        }

        return $this->checkPermission($permission, $permissionValue);
    }

    public function checkPermission(int $requiredPermission, int $permission): bool
    {
        if (
            $requiredPermission !== PermissionEnum::DENIED->value
            && ($permission & PermissionEnum::DENIED->value)
        ) {
            return false;
        }

        return ($permission & $requiredPermission) === $requiredPermission;
    }

    public function isDenied(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        return $this->hasPermission(PermissionEnum::DENIED->value, $module, $task, $action, $method, $userId);
    }

    public function hasReadPermission(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        return $this->hasPermission(PermissionEnum::READ->value, $module, $task, $action, $method, $userId);
    }

    public function hasWritePermission(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        return $this->hasPermission(PermissionEnum::WRITE->value, $module, $task, $action, $method, $userId);
    }

    public function hasDeletePermission(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        return $this->hasPermission(PermissionEnum::DELETE->value, $module, $task, $action, $method, $userId);
    }

    public function hasManagePermission(
        string $module,
        ?string $task = null,
        ?string $action = null,
        ?HttpMethod $method = null,
        ?int $userId = null,
    ): bool {
        return $this->hasPermission(PermissionEnum::MANAGE->value, $module, $task, $action, $method, $userId);
    }

    /**
     * @param array<string, array{permissionRequired: bool, method: string, items: array}> $requiredPermissions
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
     * @param array{permissionRequired: bool, method: string, items: array} $permissionItem
     */
    private function getRequiredPermissionItem(
        array $permissionItem,
        ?int $userId,
        string $module,
        ?string $task = null,
        ?string $action = null,
    ): array {
        $permissions = [];

        if ($permissionItem['permissionRequired'] ?? false) {
            try {
                $permissions['permission'] = $this->getPermission(
                    $module,
                    $task,
                    $action,
                    isset($permissionItem['method']) ? HttpMethod::from(strtoupper($permissionItem['method'])) : null,
                    $userId,
                );
            } catch (SelectError) {
                $permissions['permission'] = PermissionEnum::DENIED->value;
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

                $permission = $this->getRequiredPermissionItem(
                    $item,
                    $userId,
                    $module,
                    $itemTask,
                    $itemAction,
                );

                if (!isset($item['method'])) {
                    $permissions['items'][$key] = $permission;

                    continue;
                }

                if (!isset($permissions['items'][$key])) {
                    $permissions['items'][$key] = [];
                }

                $permissions['items'][$key][$item['method']] = $permission;
            }
        }

        return $permissions;
    }
}
