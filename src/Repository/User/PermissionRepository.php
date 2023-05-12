<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

class PermissionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByModuleTaskAndAction(Module $module, Task $task, Action $action, int $userId = null): Permission
    {
        return $this->fetchOne(
            '`module_id`=? AND `task_id`=? AND `action_id`=? AND IFNULL(`user_id`, ?)=?',
            [$module->getId(), $task->getId(), $action->getId(), 0, $userId ?? 0],
            Permission::class
        );
    }

    /**
     * @throws SelectError
     */
    public function getByModuleAndTask(Module $module, Task $task, int $userId = null): Permission
    {
        return $this->fetchOne(
            '`module_id`=? AND `task_id`=? AND `action_id` IS NULL AND IFNULL(`user_id`, ?)=?',
            [$module->getId(), $task->getId(), 0, $userId ?? 0],
            Permission::class
        );
    }
}
