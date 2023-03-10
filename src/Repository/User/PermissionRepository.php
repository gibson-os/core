<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

class PermissionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByModuleTaskAndAction(string $module, string $task, string $action, int $userId = null): Permission
    {
        return $this->fetchOne(
            '`module`=? AND `task`=? AND `action`=? AND IFNULL(`user_id`, ?)=?',
            [$module, $task, $action, 0, $userId ?? 0],
            Permission::class
        );
    }

    /**
     * @throws SelectError
     */
    public function getByModuleAndTask(string $module, string $task, int $userId = null): Permission
    {
        return $this->fetchOne(
            '`module`=? AND `task`=? AND `action` IS NULL AND IFNULL(`user_id`, ?)=?',
            [$module, $task, 0, $userId ?? 0],
            Permission::class
        );
    }
}
