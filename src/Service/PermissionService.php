<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionRepository;

class PermissionService
{
    public const INHERIT = 0; // 00000

    public const DENIED = 1;  // 00001

    public const READ = 2;    // 00010

    public const WRITE = 4;   // 00100

    public const DELETE = 8;  // 01000

    public const MANAGE = 16;

    public function __construct(private PermissionRepository $permissionRepository)
    {
    }

    /**
     * @throws DateTimeError
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

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function hasPermission(
        int $permission,
        string $module,
        string $task = null,
        string $action = null,
        int $userId = null
    ): bool {
        $permissionValue = $this->getPermission($module, $task, $action, $userId);

        if (
            $permission !== self::DENIED &&
            ($permissionValue & self::DENIED)
        ) {
            return false;
        }

        return ($permissionValue & $permission) === $permission;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function isDenied(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::DENIED, $module, $task, $action, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function hasReadPermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::READ, $module, $task, $action, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function hasWritePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::WRITE, $module, $task, $action, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function hasDeletePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::DELETE, $module, $task, $action, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function hasManagePermission(string $module, string $task = null, string $action = null, int $userId = null): bool
    {
        return $this->hasPermission(self::MANAGE, $module, $task, $action, $userId);
    }
}
