<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

class PermissionRepository extends AbstractRepository
{
    public function getPermissionByModule(string $module, int $userId = null): Permission
    {
        $table = $this->getTable(Permission::getTableName());
        $table->setWhere(
            $this->getUserIdWhere($userId) . ' AND ' .
            $this->getModuleWhere($module)
        );
    }

    public function getPermissionByTask(string $module, string $task, int $userId = null): Permission
    {
        $table = $this->getTable(Permission::getTableName());
        $table->setWhere(
            $this->getUserIdWhere($userId) . ' AND ((' .
            $this->getModuleWhere($module) . ' AND ' .
            $this->getTaskWhere($task) .
            ') OR (' .
            $this->getModuleWhere($module) . ' AND ' .
            $this->getTaskWhere('') .
            '))'
        );
    }

    public function getPermissionByAction(string $module, string $task, string $action, int $userId = null): Permission
    {
        $table = $this->getTable(Permission::getTableName());
        $table->setWhere(
            $this->getUserIdWhere($userId) . ' AND ((' .
            $this->getModuleWhere($module) . ' AND ' .
            $this->getTaskWhere($task) . ' AND ' .
            $this->getActionWhere($action) .
            ') OR (' .
            $this->getModuleWhere($module) . ' AND ' .
            $this->getTaskWhere($task) . ' AND ' .
            $this->getActionWhere('') .
            ') OR (' .
            $this->getModuleWhere($module) . ' AND ' .
            $this->getTaskWhere('') . ' AND ' .
            $this->getActionWhere('') .
            '))'
        );
    }

    private function getModuleWhere(string $module): string
    {
        return '`module`=' . $this->escape($module);
    }

    private function getTaskWhere(string $task): string
    {
        return '`task`=' . $this->escape($task);
    }

    private function getActionWhere(string $action): string
    {
        return '`action`=' . $this->escape($action);
    }

    private function getUserIdWhere(int $userId = null): string
    {
        return '(`user_id`=0' . ($userId === null ? '' : ' OR `user_id`=' . $userId) . ')';
    }
}
