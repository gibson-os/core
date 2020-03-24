<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Repository\User\PermissionRepository;

class PermissionService
{
    public const INHERIT = 0; // 00000

    public const DENIED = 1;  // 00001

    public const READ = 2;    // 00010

    public const WRITE = 4;   // 00100

    public const DELETE = 8;  // 01000

    public const MANAGE = 16; // 10000

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

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
        return ($this->getPermission($module, $task, $action, $userId) & $permission) === $permission;
    }

    public function isDenied(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::DENIED, $module, $task, $action, $userId);
    }

    public function isRead(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::READ, $module, $task, $action, $userId);
    }

    public function isWrite(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::WRITE, $module, $task, $action, $userId);
    }

    public function isDelete(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::DELETE, $module, $task, $action, $userId);
    }

    public function isManage(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::MANAGE, $module, $task, $action, $userId);
    }
}
