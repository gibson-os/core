<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\User;

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
            $this->addWhere('`module_id`=? AND `task_id` IS NULL AND `action_id` IS NULL', [$this->moduleId]);
        } elseif ($this->taskId !== null) {
            $this->addWhere('`task_id`=? AND `action_id` IS NULL', [$this->taskId]);
        } elseif ($this->actionId !== null) {
            $this->addWhere('`action_id`=?', [$this->actionId]);
        }
    }

    protected function getDefaultOrder(): string
    {
        return '`user_ip`, `user_host`, `user_name`';
    }

    public function getList(): \Generator
    {
        /** @var PermissionView $permissionView */
        foreach (parent::getList() as $permissionView) {
            $permissionViewData = $permissionView->jsonSerialize();
            $permissionViewData['parentPermission'] = 0;

            if ($this->isParentPermission($permissionView)) {
                $permissionViewData['parentPermission'] = $permissionView->getPermission();
                $permissionViewData['permission'] = 0;
            }

            yield $permissionViewData;
        }
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
        if ($this->taskId !== null && $permissionView->getTask() === null) {
            return true;
        }

        if ($this->actionId !== null && $permissionView->getAction() === null) {
            return true;
        }

        return false;
    }
}
