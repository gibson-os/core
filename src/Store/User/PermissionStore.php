<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\User;

use Generator;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\PermissionView;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class PermissionStore extends AbstractDatabaseStore
{
    private ?int $moduleId = null;

    private ?int $taskId = null;

    private ?int $actionId = null;

    protected function getModelClassName(): string
    {
        return PermissionView::class;
    }

    protected function setWheres(): void
    {
        if ($this->moduleId !== null) {
            $this->addWhere('`module_id`=? AND `task`=? AND `action`=?', [$this->moduleId, '', '']);
        }

        if ($this->taskId !== null) {
            $this->addWhere('`task_id`=? AND `action`=?', [$this->taskId, '']);
        }

        if ($this->actionId !== null) {
            $this->addWhere('`action_id`=?', [$this->actionId, 1]);
        }
    }

    protected function getDefaultOrder(): string
    {
        return '`user_ip`, `user_host`, `user_name`';
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table->appendJoin(User::getTableName(), '`user`.`id`=`view_user_permission`.`user_id`');
    }

    public function getList(): Generator
    {
        $this->initTable();
        $select = 'DISTINCT ' .
            '`user_id` `userId`, ' .
            '`user`.`user` `userName`, ' .
            '`user`.`host` `userHost`, ' .
            '`user`.`ip` `userIp`, ' .
            'IFNULL(`permission`, 1) `permission`, ' .
            '`module`, ' .
            '`task`, ' .
            '`action`'
        ;

        if ($this->table->selectPrepared(false, $select) === false) {
            $exception = new SelectError($this->table->connection->error());
            $exception->setTable($this->table);

            throw $exception;
        }

        foreach ($this->table->connection->fetchAssocList() as $permissionView) {
            $permissionView['parentPermission'] = 0;

            if ($this->isParentPermission($permissionView)) {
                $permissionView['parentPermission'] = $permissionView['permission'];
                $permissionView['permission'] = 0;
            }

            yield $permissionView;
        }
        error_log($this->table->sql);
    }

    public function setModuleId(?int $moduleId): PermissionStore
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function setTaskId(?int $taskId): PermissionStore
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function setActionId(?int $actionId): PermissionStore
    {
        $this->actionId = $actionId;

        return $this;
    }

    private function isParentPermission(array $permissionView): bool
    {
        if ($this->taskId !== null && $permissionView['task'] === '') {
            return true;
        }

        if ($this->actionId !== null && $permissionView['action'] === '') {
            return true;
        }

        return false;
    }
}
