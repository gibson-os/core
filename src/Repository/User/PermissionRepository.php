<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\AbstractRepository;
use mysqlDatabase;
use mysqlTable;

class PermissionRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    public function getPermissionByModule(string $module, int $userId = null): Permission
    {
        $table = $this->getTable(Permission::getTableName());
        $table->setWhere(
            $this->getUserIdWhere($userId) . ' AND ' .
            $this->getModuleWhere($module)
        );

        if (!$table->select()) {
            $exception = new SelectError(sprintf('Permission für %s nicht gefunden!', $module));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getPermission($table);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
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

        if (!$table->select()) {
            $exception = new SelectError(sprintf('Permission für %s/%s nicht gefunden!', $module, $task));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getPermission($table);
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     */
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

        if (!$table->select()) {
            $exception = new SelectError(sprintf('Permission für %s/%s::%s nicht gefunden!', $module, $task, $action));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getPermission($table);
    }

    protected function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $table = parent::getTable($tableName, $database);
        $table->setOrderBy('`task` DESC, `action` DESC, `user_id` DESC');
        $table->setLimit(1);

        return $table;
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

    /**
     * @throws DateTimeError
     * @throws GetError
     */
    private function getPermission(mysqlTable $table): Permission
    {
        $permission = new Permission();
        $permission->loadFromMysqlTable($table);

        return $permission;
    }
}
