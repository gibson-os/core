<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use GibsonOS\Core\Attribute\Install\Database\View;
use GibsonOS\Core\Model\AbstractModel;
use JsonSerializable;

#[View(self::QUERY, 'view_user_permission')]
class PermissionView extends AbstractModel implements JsonSerializable
{
    private const QUERY =
        'WITH `all_users` AS (' .
            '(' .
                'SELECT ' .
                '0 `id`, ' .
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
                '`rp`.`module_id` AS `module_id`, ' .
                '`rp`.`task_id` AS `task_id`, ' .
                '`rp`.`action_id` AS `action_id`, ' .
                'MAX(`rp`.`permission`) AS `permission` ' .
            'FROM `role_user` `ru` ' .
            'JOIN `role_permission` `rp` ON `rp`.`role_id` = `ru`.`role_id` ' .
            'GROUP BY `ru`.`user_id`, `rp`.`module_id`, `rp`.`task_id`, `rp`.`action_id`' .
        ') ' .
        'SELECT ' .
            'CAST(`p`.`user_id` AS UNSIGNED) `user_id`, ' .
            '`p`.`user_name`, ' .
            '`p`.`user_host`, ' .
            '`p`.`user_ip`, ' .
            'CAST(`p`.`permission` AS UNSIGNED) `permission`, ' .
            '`p`.`permission_module_id`, ' .
            '`p`.`permission_task_id`, ' .
            '`p`.`permission_action_id`, ' .
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
                    'IFNULL(`upm`.`permission`, IFNULL(`mrp`.`permission`, ' . Permission::DENIED . ')) `permission`, ' .
                    '`upm`.`module_id` `permission_module_id`, ' .
                    '`upm`.`task_id` `permission_task_id`, ' .
                    '`upm`.`action_id` `permission_action_id`, ' .
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
                    '`mrp`.`module_id`=`m`.`id` AND ' .
                    '`mrp`.`task_id` IS NULL AND ' .
                    '`mrp`.`action_id` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` AND ' .
                    '`upm`.`task_id` IS NULL AND ' .
                    '`upm`.`action_id` IS NULL ' .
            ') UNION ALL (' .
                'SELECT DISTINCT ' .
                    '`u`.`id` `user_id`, ' .
                    '`u`.`user` `user_name`, ' .
                    '`u`.`host` `user_host`, ' .
                    '`u`.`ip` `user_ip`, ' .
                    'IFNULL(' .
                        '`upt`.`permission`, ' .
                        'IFNULL(' .
                            '`upm`.`permission`, ' .
                            'IFNULL(' .
                                '`mrpt`.`permission`, ' .
                                'IFNULL(`mrpm`.`permission`, ' . Permission::DENIED . ')' .
                            ')' .
                        ')' .
                    ') `permission`, ' .
                    'IFNULL(`upt`.`module_id`, `upm`.`module_id`) `module_id`, ' .
                    'IFNULL(`upt`.`task_id`, `upm`.`task_id`) `task_id`, ' .
                    'IFNULL(`upt`.`action_id`, `upm`.`action_id`) `action_id`, ' .
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
                    '`mrpm`.`module_id`=`m`.`id` AND ' .
                    '`mrpm`.`task_id` IS NULL AND ' .
                    '`mrpm`.`action_id` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` AND ' .
                    '`upm`.`task_id` IS NULL AND ' .
                    '`upm`.`action_id` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpt` ON ' .
                    '`u`.`id`=`mrpt`.`user_id` AND ' .
                    '`mrpt`.`module_id`=`m`.`id` AND ' .
                    '`mrpt`.`task_id`=`t`.`id` AND ' .
                    '`mrpt`.`action_id` IS NULL ' .
                'LEFT JOIN `user_permission` `upt` ON ' .
                    '`u`.`id`=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`module_id`=`m`.`id` AND ' .
                    '`upt`.`task_id`=`t`.`id` AND ' .
                    '`upt`.`action_id` IS NULL ' .
            ') UNION ALL (' .
                'SELECT DISTINCT ' .
                    '`u`.`id` `user_id`, ' .
                    '`u`.`user` `user_name`, ' .
                    '`u`.`host` `user_host`, ' .
                    '`u`.`ip` `user_ip`, ' .
                    'IFNULL(' .
                        '`upa`.`permission`, ' .
                        'IFNULL(' .
                            '`upt`.`permission`, ' .
                            'IFNULL(' .
                                '`upm`.`permission`, ' .
                                'IFNULL(' .
                                    '`mrpa`.`permission`, ' .
                                    'IFNULL(' .
                                        '`mrpt`.`permission`, 
                                        IFNULL(`mrpm`.`permission`, ' . Permission::DENIED . ')' .
                                    ')' .
                                ')' .
                            ')' .
                        ')' .
                    ') `permission`, ' .
                    'IFNULL(`upa`.`module_id`, IFNULL(`upt`.`module_id`, `upm`.`module_id`)) `module_id`, ' .
                    'IFNULL(`upa`.`task_id`, IFNULL(`upt`.`task_id`, `upm`.`task_id`)) `task_id`, ' .
                    'IFNULL(`upa`.`action_id`, IFNULL(`upt`.`action_id`, `upm`.`action_id`)) `action_id`, ' .
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
                    '`mrpm`.`module_id`=`m`.`id` AND ' .
                    '`mrpm`.`task_id` IS NULL AND ' .
                    '`mrpm`.`action_id` IS NULL ' .
                'LEFT JOIN `user_permission` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` AND ' .
                    '`upm`.`task_id` IS NULL AND ' .
                    '`upm`.`action_id` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpt` ON ' .
                    '`u`.`id`=`mrpt`.`user_id` AND ' .
                    '`mrpt`.`module_id`=`m`.`id` AND ' .
                    '`mrpt`.`task_id`=`t`.`id` AND ' .
                    '`mrpt`.`action_id` IS NULL ' .
                'LEFT JOIN `user_permission` `upt` ON ' .
                    '`u`.`id`=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`module_id`=`m`.`id` AND ' .
                    '`upt`.`task_id`=`t`.`id` AND ' .
                    '`upt`.`action_id` IS NULL ' .
                'LEFT JOIN `max_role_permission` `mrpa` ON ' .
                    '`u`.`id`=`mrpa`.`user_id` AND ' .
                    '`mrpa`.`module_id`=`m`.`id` AND ' .
                    '`mrpa`.`task_id`=`t`.`id` AND ' .
                    '`mrpa`.`action_id`=`a`.`id` ' .
                'LEFT JOIN `user_permission` `upa` ON ' .
                    '`u`.`id`=IFNULL(`upa`.`user_id`, 0) AND ' .
                    '`upa`.`module_id`=`m`.`id` AND ' .
                    '`upa`.`task_id`=`t`.`id` AND ' .
                    '`upa`.`action_id`=`a`.`id` ' .
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
