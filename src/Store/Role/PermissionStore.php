<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Role;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\Permission;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission as UserPermission;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use mysqlDatabase;

/**
 * @extends AbstractDatabaseStore<Role>
 */
class PermissionStore extends AbstractDatabaseStore
{
    private ?int $moduleId = null;

    private ?int $taskId = null;

    private ?int $actionId = null;

    public function __construct(
        #[GetTableName(Permission::class)] private readonly string $permissionTableName,
        #[GetTableName(Module::class)] private readonly string $moduleTableName,
        #[GetTableName(Task::class)] private readonly string $taskTableName,
        #[GetTableName(Action::class)] private readonly string $actionTableName,
        mysqlDatabase $mysqlDatabase
    ) {
        parent::__construct($mysqlDatabase);
    }

    protected function getModelClassName(): string
    {
        return Role::class;
    }

    protected function getDefaultOrder(): string
    {
        return sprintf('`%s`.`name`', $this->tableName);
    }

    protected function initTable(): void
    {
        parent::initTable();

        $selects = [
            sprintf('`%s`.`id` `roleId`', $this->tableName),
            sprintf('`%s`.`name` `roleName`', $this->tableName),
            '`upm`.`id` `modulePermissionId`',
            '`upm`.`permission` `modulePermission`',
            '`m`.`name` `moduleName`',
        ];
        $parameters = [];

        if ($this->moduleId !== null) {
            $this->table
                ->appendJoinLeft(sprintf('`%s` `m`', $this->moduleTableName), '`m`.`id`=?')
                ->appendJoinLeft(
                    sprintf('`%s` `upm`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upm`.`role_id` AND `upm`.`module_id`=`m`.`id` AND `upm`.`task_id` IS NULL', $this->tableName),
                )
            ;

            $parameters = [$this->moduleId];
            $selects[] = 'NULL `taskName`';
            $selects[] = 'NULL `actionName`';
            $selects[] = '`upm`.`id` `id`';
            $selects[] = '`upm`.`permission` `permission`';
            $selects[] = 'NULL `parentId`';
            $selects[] = sprintf('%d `parentPermission`', UserPermission::DENIED);
            $selects[] = 'NULL `taskPermissionId`';
            $selects[] = 'NULL `taskPermission`';
            $selects[] = 'NULL `actionPermissionId`';
            $selects[] = 'NULL `actionPermission`';
        }

        if ($this->taskId !== null) {
            $this->table
                ->appendJoinLeft(sprintf('`%s` `t`', $this->taskTableName), '`t`.`id`=?')
                ->appendJoinLeft(
                    sprintf('`%s` `upt`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upt`.`role_id` AND `upt`.`task_id`=`t`.`id` AND `upt`.`action_id` IS NULL', $this->tableName),
                )
                ->appendJoinLeft(sprintf('`%s` `m`', $this->moduleTableName), '`m`.`id`=`t`.`module_id`')
                ->appendJoinLeft(
                    sprintf('`%s` `upm`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upm`.`role_id` AND `upm`.`module_id`=`m`.`id` AND `upm`.`task_id` IS NULL', $this->tableName),
                )
            ;

            $parameters = [$this->taskId];
            $selects[] = '`t`.`name` `taskName`';
            $selects[] = 'NULL `actionName`';
            $selects[] = '`upt`.`id` `id`';
            $selects[] = '`upt`.`permission` `permission`';
            $selects[] = '`upm`.`id` `parentId`';
            $selects[] = sprintf('IFNULL(`upm`.`permission`, %d) `parentPermission`', UserPermission::DENIED);
            $selects[] = '`upt`.`id` `taskPermissionId`';
            $selects[] = '`upt`.`permission` `taskPermission`';
            $selects[] = 'NULL `actionPermissionId`';
            $selects[] = 'NULL `actionPermission`';
        }

        if ($this->actionId !== null) {
            $this->table
                ->appendJoinLeft(sprintf('`%s` `a`', $this->actionTableName), '`a`.`id`=?')
                ->appendJoinLeft(
                    sprintf('`%s` `upa`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upa`.`role_id` AND `upa`.`action_id`=`a`.`id`', $this->tableName)
                )
                ->appendJoinLeft(sprintf('`%s` `t`', $this->taskTableName), '`t`.`id`=`a`.`task_id`')
                ->appendJoinLeft(
                    sprintf('`%s` `upt`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upt`.`role_id` AND `upt`.`task_id`=`t`.`id` AND `upt`.`action_id` IS NULL', $this->tableName)
                )
                ->appendJoinLeft(sprintf('`%s` `m`', $this->moduleTableName), '`m`.`id`=`t`.`module_id`')
                ->appendJoinLeft(
                    sprintf('`%s` `upm`', $this->permissionTableName),
                    sprintf('`%s`.`id`=`upm`.`role_id` AND `upm`.`module_id`=`m`.`id` AND `upm`.`task_id` IS NULL', $this->tableName)
                )
            ;

            $parameters = [$this->actionId];
            $selects[] = '`t`.`name` `taskName`';
            $selects[] = '`a`.`name` `actionName`';
            $selects[] = '`upa`.`id` `id`';
            $selects[] = '`upa`.`permission` `permission`';
            $selects[] = 'IFNULL(`upt`.`id`, `upm`.`id`) `parentId`';
            $selects[] = sprintf(
                'IFNULL(IFNULL(`upt`.`permission`, `upm`.`permission`), %d) `parentPermission`',
                UserPermission::DENIED,
            );
            $selects[] = '`upt`.`id` `taskPermissionId`';
            $selects[] = '`upt`.`permission` `taskPermission`';
            $selects[] = '`upa`.`id` `actionPermissionId`';
            $selects[] = '`upa`.`permission` `actionPermission`';
        }

        $this->table
            ->setWhereParameters($parameters)
            ->setSelectString(implode(', ', $selects))
        ;
    }

    /**
     * @throws SelectError
     *
     * @return iterable<string[]>
     */
    protected function getModels(): iterable
    {
        if ($this->table->selectPrepared() === false) {
            $exception = new SelectError($this->table->connection->error());
            $exception->setTable($this->table);

            throw $exception;
        }

        if ($this->table->countRecords() === 0) {
            return;
        }

        do {
            yield $this->table->getSelectedRecord();
        } while ($this->table->next());
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
}
