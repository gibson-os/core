<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\User;

use Generator;
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
            $this->addWhere(
                '`module_id`=? AND `task`=? AND `action`=? AND `module_row_number`=?',
                [$this->moduleId, '', '', 1]
            );
        }

        if ($this->taskId !== null) {
            $this->addWhere('`task_id`=? AND `action`=? AND `module_row_number`=?', [$this->taskId, '', 1]);
        }

        if ($this->actionId !== null) {
            $this->addWhere('`action_id`=?', [$this->actionId, 1]);
        }
    }

    protected function getDefaultOrder(): string
    {
        return '`user_ip`, `user_host`, `user_name`';
    }

    public function getList(): Generator
    {
        /** @var PermissionView $permissionView */
        foreach (parent::getList() as $permissionView) {
            $data = $permissionView->jsonSerialize();
            $data['parentPermission'] = 0;

            if ($this->isParentPermission($permissionView)) {
                $data['parentPermission'] = $data['permission'];
                $data['permission'] = 0;
            }

            yield $data;
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

    private function isParentPermission(PermissionView $permissionView): bool
    {
        if ($this->taskId !== null && $permissionView->getTaskId() === null) {
            return true;
        }

        if ($this->actionId !== null && $permissionView->getActionId() === null) {
            return true;
        }

        return false;
    }
}
