<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\AbstractRepository;
use mysqlDatabase;
use mysqlTable;

/**
 * @method Permission getModel(mysqlTable $table, string $abstractModelClassName)
 */
class PermissionRepository extends AbstractRepository
{
    /**
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

        return $this->getModel($table, Permission::class);
    }

    /**
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

        return $this->getModel($table, Permission::class);
    }

    /**
     * @throws SelectError
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

        return $this->getModel($table, Permission::class);
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
        $table->addWhereParameter(0);
        $table->addWhereParameter($userId ?? 0);

        return '(`user_id`=?' . ($userId === null ? '' : ' OR `user_id`=?') . ')';
    }
}
