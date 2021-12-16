<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Model\User\PermissionView;
use GibsonOS\Core\Repository\AbstractRepository;
use mysqlTable;
use stdClass;

class PermissionViewRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return stdClass[]
     */
    public function getTaskList(?int $userId, string $module = null): array
    {
        $table = $this->getTable(PermissionView::getTableName());
        $table
            ->setWhere(
                'IFNULL(`user_id`, ?)=? AND ' .
                '`permission`>? AND ' .
                '`task_id` IS NOT NULL' .
                ($module === null ? '' : ' AND `module_name`=?')
            )
            ->setWhereParameters([$userId ?? 0, $userId ?? 0, Permission::DENIED])
        ;

        if ($module !== null) {
            $table->addWhereParameter($module);
        }

        if (!$table->selectPrepared(false, 'DISTINCT `module_name` AS `module`, `task_name` AS `task`')) {
            throw (new SelectError())->setTable($table);
        }

        return $table->connection->fetchObjectList();
    }

    /**
     * @throws SelectError
     */
    public function getPermissionByModule(string $module, int $userId = null): PermissionView
    {
        $table = $this
            ->getTable(PermissionView::getTableName())
            ->setLimit(1)
        ;
        $table->setWhere(
            $this->getUserIdWhere($table, $userId) . ' AND ' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            '`task_name` IS NULL AND ' .
            '`action_name` IS NULL'
        );

        if (!$table->selectPrepared()) {
            $exception = new SelectError(sprintf('Permission für %s nicht gefunden!', $module));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, PermissionView::class);
    }

    /**
     * @throws SelectError
     */
    public function getPermissionByTask(string $module, string $task, int $userId = null): PermissionView
    {
        $table = $this
            ->getTable(PermissionView::getTableName())
            ->setLimit(1)
        ;
        $table->setWhere(
            $this->getUserIdWhere($table, $userId) . ' AND ' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, $task) . ' AND ' .
            '`action_name` IS NULL'
        );

        if (!$table->selectPrepared()) {
            $exception = new SelectError(sprintf('Permission für %s/%s nicht gefunden!', $module, $task));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, PermissionView::class);
    }

    /**
     * @throws SelectError
     */
    public function getPermissionByAction(string $module, string $task, string $action, int $userId = null): PermissionView
    {
        $table = $this
            ->getTable(PermissionView::getTableName())
            ->setLimit(1)
        ;
        $table->setWhere(
            $this->getUserIdWhere($table, $userId) . ' AND ' .
            $this->getModuleWhere($table, $module) . ' AND ' .
            $this->getTaskWhere($table, $task) . ' AND ' .
            $this->getActionWhere($table, $action)
        );

        if (!$table->selectPrepared()) {
            $exception = new SelectError(sprintf('Permission für %s/%s::%s nicht gefunden!', $module, $task, $action));
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, PermissionView::class);
    }

    private function getModuleWhere(mysqlTable $table, string $module): string
    {
        $table->addWhereParameter($module);

        return '`module_name`=?';
    }

    private function getTaskWhere(mysqlTable $table, string $task): string
    {
        $table->addWhereParameter($task);

        return '`task_name`=?';
    }

    private function getActionWhere(mysqlTable $table, string $action): string
    {
        $table->addWhereParameter($action);

        return '`action_name`=?';
    }

    private function getUserIdWhere(mysqlTable $table, int $userId = null): string
    {
        $table->addWhereParameter($userId ?? 0);
        $table->addWhereParameter($userId ?? 0);

        return 'IFNULL(`user_id`, ?)=?';
    }
}
