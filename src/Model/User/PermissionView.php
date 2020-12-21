<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use GibsonOS\Core\Model\AbstractModel;

class PermissionView extends AbstractModel
{
    private int $userId;

    private int $permission;

    private string $module;

    private string $task;

    private string $action;

    private ?int $moduleId = null;

    private ?string $moduleName = null;

    private ?int $taskId = null;

    private ?string $taskName = null;

    private ?int $actionId = null;

    private ?string $actionName = null;

    public static function getTableName(): string
    {
        return 'view_user_permission';
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): PermissionView
    {
        $this->userId = $userId;

        return $this;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function setPermission(int $permission): PermissionView
    {
        $this->permission = $permission;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): PermissionView
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): PermissionView
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): PermissionView
    {
        $this->action = $action;

        return $this;
    }

    public function getModuleId(): ?int
    {
        return $this->moduleId;
    }

    public function setModuleId(?int $moduleId): PermissionView
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    public function setModuleName(?string $moduleName): PermissionView
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function setTaskId(?int $taskId): PermissionView
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getTaskName(): ?string
    {
        return $this->taskName;
    }

    public function setTaskName(?string $taskName): PermissionView
    {
        $this->taskName = $taskName;

        return $this;
    }

    public function getActionId(): ?int
    {
        return $this->actionId;
    }

    public function setActionId(?int $actionId): PermissionView
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    public function setActionName(?string $actionName): PermissionView
    {
        $this->actionName = $actionName;

        return $this;
    }
}
