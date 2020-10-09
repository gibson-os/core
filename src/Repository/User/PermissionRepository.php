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
            $this->getUserIdWhere($table, $userId) . ' AND ' .
            $this->getModuleWhere($table, $module)
        );

        if (!$table->selectPrepared()) {
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
            $this->getUserIdWhere($table, $userId) . ' AND ((' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, $task) .
            ') OR (' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, '') .
            '))'
        );

        if (!$table->selectPrepared()) {
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
            $this->getUserIdWhere($table, $userId) . ' AND ((' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, $task) . ' AND ' .
            $this->getActionWhere($table, $action) .
            ') OR (' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, $task) . ' AND ' .
            $this->getActionWhere($table, '') .
            ') OR (' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, '') . ' AND ' .
            $this->getActionWhere($table, '') .
            '))'
        );

        if (!$table->selectPrepared()) {
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

    private function getModuleWhere(mysqlTable $table, string $module): string
    {
        $table->addWhereParameter($module);

        return '`module`=?';
    }

    private function getTaskWhere(mysqlTable $table, string $task): string
    {
        $table->addWhereParameter($task);

        return '`task`=?';
    }

    private function getActionWhere(mysqlTable $table, string $action): string
    {
        $table->addWhereParameter($action);

        return '`action`=?';
    }

    private function getUserIdWhere(mysqlTable $table, int $userId = null): string
    {
        if ($userId !== null) {
            $table->addWhereParameter($userId);
        }

        return '(`user_id`=0' . ($userId === null ? '' : ' OR `user_id`=?') . ')';
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
