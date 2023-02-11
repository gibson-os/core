<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use GibsonOS\Core\Attribute\Install\Database\View;
use GibsonOS\Core\Model\AbstractModel;

#[View(self::QUERY, 'view_user_permission')]
class PermissionView extends AbstractModel implements \JsonSerializable
{
    private const QUERY =
        'WITH `all_users` AS (' .
            '(' .
                'SELECT ' .
                'NULL `id`, ' .
                '\'Allgemein\' `user`, ' .
                'NULL `host`, ' .
                'NULL `ip`, ' .
                'NULL `password`, ' .
                'NULL `last_login`, ' .
                'NULL `added`' .
            ') UNION ALL (' .
                'SELECT * ' .
                'FROM user' .
            ')' .
        '), `max_role_permission` AS (' .
            'SELECT ' .
                '`ru`.`user_id` AS `user_id`, ' .
                '`rp`.`module` AS `module`, ' .
                '`rp`.`task` AS `task`, ' .
                '`rp`.`action` AS `action`, ' .
                'MAX(`rp`.`permission`) AS `permission` ' .
            'FROM `role_user` `ru` ' .
            'JOIN `role_permission` `rp` ON `rp`.`role_id` = `ru`.`role_id` ' .
            'GROUP BY `ru`.`user_id`' .
        ') ' .
        'SELECT ' .
            'CAST(`p`.`user_id` AS UNSIGNED) `user_id`, ' .
            '`p`.`user_name`, ' .
            '`p`.`user_host`, ' .
            '`p`.`user_ip`, ' .
            'CAST(`p`.`permission` AS UNSIGNED) `permission`, ' .
            '`p`.`module`, ' .
            '`p`.`task`, ' .
            '`p`.`action`, ' .
            '`p`.`module_id`, ' .
            '`p`.`module_name`, ' .
            'CAST(`p`.`task_id` AS UNSIGNED) `task_id`, ' .
            '`p`.`task_name`, ' .
            'CAST(`p`.`action_id` AS UNSIGNED) `action_id`, ' .
            '`p`.`action_name` ' .
        'FROM (' .
            '(' .
                'SELECT DISTINCT ' .
                    '`u`.`id` `user_id`, ' .
                    '`u`.`user` `user_name`, ' .
                    '`u`.`host` `user_host`, ' .
                    '`u`.`ip` `user_ip`, ' .
                    'GREATEST(' .
                        'IFNULL(`upm`.`permission`, ' . Permission::DENIED . '), ' .
                        'IFNULL(`mrp`.`permission`, ' . Permission::DENIED . ')' .
                    ') `permission`, ' .
                    '`upm`.`module` `module`, ' .
                    '`upm`.`task` `task`, ' .
                    '`upm`.`action` `action`, ' .
                    '`m`.`id` `module_id`, ' .
                    '`m`.`name` `module_name`, ' .
                    'NULL `task_id`, ' .
                    'NULL `task_name`, ' .
                    'NULL `action_id`, ' .
                    'NULL `action_name` ' .
                'FROM `module` `m` ' .
                'JOIN `all_users` `u` ON 1 ' .
                'LEFT JOIN `max_role_permission` `mrp` ON ' .
                    '`u`.`id`=`mrp`.`user_id` AND ' .
                    '`mrp`.`module`=`m`.`name` AND ' .
                    '`mrp`.`task` IS NULL AND ' .
                    '`mrp`.`action` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module`=`m`.`name` AND ' .
                    '`upm`.`task` IS NULL AND ' .
                    '`upm`.`action` IS NULL ' .
            ') UNION ALL (' .
                'SELECT DISTINCT ' .
                    '`u`.`id` `user_id`, ' .
                    '`u`.`user` `user_name`, ' .
                    '`u`.`host` `user_host`, ' .
                    '`u`.`ip` `user_ip`, ' .
                    'GREATEST(' .
                        'IFNULL(`upt`.`permission`, IFNULL(`upm`.`permission`, ' . Permission::DENIED . ')), ' .
                        'IFNULL(`mrpt`.`permission`, IFNULL(`mrpm`.`permission`, ' . Permission::DENIED . '))' .
                    ') `permission`, ' .
                    'IFNULL(`upt`.`module`, `upm`.`module`) `module`, ' .
                    'IFNULL(`upt`.`task`, `upm`.`task`) `task`, ' .
                    'IFNULL(`upt`.`action`, `upm`.`action`) `action`, ' .
                    '`m`.`id` `module_id`, ' .
                    '`m`.`name` `module_name`, ' .
                    '`t`.`id` `task_id`, ' .
                    '`t`.`name` `task_name`, ' .
                    'NULL `action_id`, ' .
                    'NULL `action_name` ' .
                'FROM `module` `m` ' .
                'LEFT JOIN `task` `t` ON `m`.`id` = `t`.`module_id` ' .
                'JOIN `all_users` `u` ON 1 ' .
                'LEFT JOIN `max_role_permission` `mrpm` ON ' .
                    '`u`.`id`=`mrpm`.`user_id` AND ' .
                    '`mrpm`.`module`=`m`.`name` AND ' .
                    '`mrpm`.`task` IS NULL AND ' .
                    '`mrpm`.`action` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module`=`m`.`name` AND ' .
                    '`upm`.`task` IS NULL AND ' .
                    '`upm`.`action` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpt` ON ' .
                    '`u`.`id`=`mrpt`.`user_id` AND ' .
                    '`mrpt`.`module`=`m`.`name` AND ' .
                    '`mrpt`.`task`=`t`.`name` AND ' .
                    '`mrpt`.`action` IS NULL ' .
                'LEFT JOIN `user_permission` `upt` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`module`=`m`.`name` AND ' .
                    '`upt`.`task`=`t`.`name` AND ' .
                    '`upt`.`action` IS NULL ' .
            ') UNION ALL (' .
                'SELECT DISTINCT ' .
                    '`u`.`id` `user_id`, ' .
                    '`u`.`user` `user_name`, ' .
                    '`u`.`host` `user_host`, ' .
                    '`u`.`ip` `user_ip`, ' .
                    'GREATEST(' .
                        'IFNULL(`upa`.`permission`, IFNULL(`upt`.`permission`, IFNULL(`upm`.`permission`, ' . Permission::DENIED . '))), ' .
                        'IFNULL(`mrpa`.`permission`, IFNULL(`mrpt`.`permission`, IFNULL(`mrpm`.`permission`, ' . Permission::DENIED . ')))' .
                    ') `permission`, ' .
                    'IFNULL(`upa`.`module`, IFNULL(`upt`.`module`, `upm`.`module`)) `module`, ' .
                    'IFNULL(`upa`.`task`, IFNULL(`upt`.`task`, `upm`.`task`)) `task`, ' .
                    'IFNULL(`upa`.`action`, IFNULL(`upt`.`action`, `upm`.`action`)) `action`, ' .
                    '`m`.`id` `module_id`, ' .
                    '`m`.`name` `module_name`, ' .
                    '`t`.`id` `task_id`, ' .
                    '`t`.`name` `task_name`, ' .
                    '`a`.`id` `action_id`, ' .
                    '`a`.`name` `action_name` ' .
                'FROM `module` `m` ' .
                'LEFT JOIN `task` `t` ON `m`.`id` = `t`.`module_id` ' .
                'LEFT JOIN `action` `a` ON `t`.`id` = `a`.`task_id` ' .
                'JOIN `all_users` `u` ON 1 ' .
                'LEFT JOIN `max_role_permission` `mrpm` ON ' .
                    '`u`.`id`=`mrpm`.`user_id` AND ' .
                    '`mrpm`.`module`=`m`.`name` AND ' .
                    '`mrpm`.`task` IS NULL AND ' .
                    '`mrpm`.`action` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module`=`m`.`name` AND ' .
                    '`upm`.`task` IS NULL AND ' .
                    '`upm`.`action` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpt` ON ' .
                    '`u`.`id`=`mrpt`.`user_id` AND ' .
                    '`mrpt`.`module`=`m`.`name` AND ' .
                    '`mrpt`.`task`=`t`.`name` AND ' .
                    '`mrpt`.`action` IS NULL ' .
                'LEFT JOIN `user_permission` `upt` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`module`=`m`.`name` AND ' .
                    '`upt`.`task`=`t`.`name` AND ' .
                    '`upt`.`action` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpa` ON ' .
                    '`u`.`id`=`mrpa`.`user_id` AND ' .
                    '`mrpa`.`module`=`m`.`name` AND ' .
                    '`mrpa`.`task`=`t`.`name` AND ' .
                    '`mrpa`.`action`=`a`.`name` ' .
                'LEFT JOIN `user_permission` `upa` ON ' .
                    'IFNULL(`u`.`id`, 0)=IFNULL(`upa`.`user_id`, 0) AND ' .
                    '`upa`.`module`=`m`.`name` AND ' .
                    '`upa`.`task`=`t`.`name` AND ' .
                    '`upa`.`action`=`a`.`name` ' .
            ')' .
        ') `p`' .
        'ORDER BY `p`.`user_id` DESC'
    ;

    private ?int $userId = null;

    private ?string $userName = null;

    private ?string $userHost = null;

    private ?string $userIp = null;

    private int $permission = Permission::DENIED;

    private ?string $module = null;

    private ?string $task = null;

    private ?string $action = null;

    private int $moduleId;

    private string $moduleName;

    private ?int $taskId = null;

    private ?string $taskName = null;

    private ?int $actionId = null;

    private ?string $actionName = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): PermissionView
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): PermissionView
    {
        $this->userName = $userName;

        return $this;
    }

    public function getUserHost(): ?string
    {
        return $this->userHost;
    }

    public function setUserHost(?string $userHost): PermissionView
    {
        $this->userHost = $userHost;

        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->userIp;
    }

    public function setUserIp(?string $userIp): PermissionView
    {
        $this->userIp = $userIp;

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

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): PermissionView
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(?string $task): PermissionView
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): PermissionView
    {
        $this->action = $action;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): PermissionView
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): PermissionView
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

    public function jsonSerialize(): array
    {
        return [
            'userId' => $this->getUserId(),
            'userName' => $this->getUserName(),
            'userHost' => $this->getUserHost(),
            'userIp' => $this->getUserIp(),
            'permission' => $this->getPermission(),
            'module' => $this->getModule(),
            'task' => $this->getTask(),
            'action' => $this->getAction(),
            'moduleId' => $this->getModuleId(),
            'moduleName' => $this->getModuleName(),
            'taskId' => $this->getTaskId(),
            'taskName' => $this->getTaskName(),
            'actionId' => $this->getActionId(),
            'actionName' => $this->getActionName(),
        ];
    }
}
