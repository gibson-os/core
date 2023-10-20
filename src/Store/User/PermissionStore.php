<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\User;

use Generator;
use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;

class PermissionStore extends AbstractStore
{
    private ?int $moduleId = null;

    private ?int $taskId = null;

    private ?int $actionId = null;

    public function __construct(
        private readonly DatabaseStoreWrapper $databaseStoreWrapper,
        #[GetTable(User::class)]
        private readonly Table $userTable,
        #[GetTableName(Permission::class)]
        private readonly string $permissionTableName,
        #[GetTableName(Module::class)]
        private readonly string $moduleTableName,
        #[GetTableName(Task::class)]
        private readonly string $taskTableName,
        #[GetTableName(Action::class)]
        private readonly string $actionTableName,
    ) {
    }

    public function getCount(): int
    {
        $selectQuery = (new SelectQuery($this->userTable))
            ->setSelects(['count' => 'COUNT(`id`)'])
        ;

        $result = $this->databaseStoreWrapper->getClient()->execute($selectQuery);

        return (int) ($result?->iterateRecords()->current()->get('count')->getValue() ?? 0);
    }

    public function getList(): Generator
    {
        $selects = [
            '`u`.`id` `userId`',
            '`u`.`user` `userName`',
            '`upm`.`id` `modulePermissionId`',
            '`upm`.`permission` `modulePermission`',
            '`m`.`id` `moduleId`',
            '`m`.`name` `moduleName`',
        ];
        $joins = [];
        $parameters = [];

        if ($this->moduleId !== null) {
            $joins = [
                sprintf('LEFT JOIN `%s` `m` ON `m`.`id`=?', $this->moduleTableName),
                sprintf(
                    'LEFT JOIN `%s` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` ' .
                    'AND `upm`.`task_id` IS NULL',
                    $this->permissionTableName,
                ),
            ];
            $parameters = [$this->moduleId];
            $selects[] = 'NULL `taskId`';
            $selects[] = 'NULL `taskName`';
            $selects[] = 'NULL `actionId`';
            $selects[] = 'NULL `actionName`';
            $selects[] = '`upm`.`id` `id`';
            $selects[] = '`upm`.`permission` `permission`';
            $selects[] = 'NULL `parentId`';
            $selects[] = sprintf('%d `parentPermission`', PermissionEnum::DENIED->value);
            $selects[] = 'NULL `taskPermissionId`';
            $selects[] = 'NULL `taskPermission`';
            $selects[] = 'NULL `actionPermissionId`';
            $selects[] = 'NULL `actionPermission`';
        }

        if ($this->taskId !== null) {
            $joins = [
                sprintf('LEFT JOIN `%s` `t` ON `t`.`id`=?', $this->taskTableName),
                sprintf(
                    'LEFT JOIN `%s` `upt` ON ' .
                    '`u`.`id`=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`task_id`=`t`.`id` AND ' .
                    '`upt`.`action_id` IS NULL',
                    $this->permissionTableName,
                ),
                sprintf('LEFT JOIN `%s` `m` ON `m`.`id`=`t`.`module_id`', $this->moduleTableName),
                sprintf(
                    'LEFT JOIN `%s` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` ' .
                    'AND `upm`.`task_id` IS NULL',
                    $this->permissionTableName,
                ),
            ];
            $parameters = [$this->taskId];
            $selects[] = '`t`.`id` `taskId`';
            $selects[] = '`t`.`name` `taskName`';
            $selects[] = 'NULL `actionId`';
            $selects[] = 'NULL `actionName`';
            $selects[] = '`upt`.`id` `id`';
            $selects[] = '`upt`.`permission` `permission`';
            $selects[] = '`upm`.`id` `parentId`';
            $selects[] = sprintf('IFNULL(`upm`.`permission`, %d) `parentPermission`', PermissionEnum::DENIED->value);
            $selects[] = '`upt`.`id` `taskPermissionId`';
            $selects[] = '`upt`.`permission` `taskPermission`';
            $selects[] = 'NULL `actionPermissionId`';
            $selects[] = 'NULL `actionPermission`';
        }

        if ($this->actionId !== null) {
            $joins = [
                sprintf('LEFT JOIN `%s` `a` ON `a`.`id`=?', $this->actionTableName),
                sprintf(
                    'LEFT JOIN `%s` `upa` ON ' .
                    '`u`.`id`=IFNULL(`upa`.`user_id`, 0) AND ' .
                    '`upa`.`action_id`=`a`.`id`',
                    $this->permissionTableName,
                ),
                sprintf('LEFT JOIN `%s` `t` ON `t`.`id`=`a`.`task_id`', $this->taskTableName),
                sprintf(
                    'LEFT JOIN `%s` `upt` ON ' .
                    '`u`.`id`=IFNULL(`upt`.`user_id`, 0) AND ' .
                    '`upt`.`task_id`=`t`.`id` AND ' .
                    '`upt`.`action_id` IS NULL',
                    $this->permissionTableName,
                ),
                sprintf('LEFT JOIN `%s` `m` ON `m`.`id`=`t`.`module_id`', $this->moduleTableName),
                sprintf(
                    'LEFT JOIN `%s` `upm` ON ' .
                    '`u`.`id`=IFNULL(`upm`.`user_id`, 0) AND ' .
                    '`upm`.`module_id`=`m`.`id` AND ' .
                    '`upm`.`task_id` IS NULL',
                    $this->permissionTableName,
                ),
            ];
            $parameters = [$this->actionId];
            $selects[] = '`t`.`id` `taskId`';
            $selects[] = '`t`.`name` `taskName`';
            $selects[] = '`a`.`id` `actionId`';
            $selects[] = '`a`.`name` `actionName`';
            $selects[] = '`upa`.`id` `id`';
            $selects[] = '`upa`.`permission` `permission`';
            $selects[] = 'IFNULL(`upt`.`id`, `upm`.`id`) `parentId`';
            $selects[] = sprintf(
                'IFNULL(IFNULL(`upt`.`permission`, `upm`.`permission`), %d) `parentPermission`',
                PermissionEnum::DENIED->value,
            );
            $selects[] = '`upt`.`id` `taskPermissionId`';
            $selects[] = '`upt`.`permission` `taskPermission`';
            $selects[] = '`upa`.`id` `actionPermissionId`';
            $selects[] = '`upa`.`permission` `actionPermission`';
        }

        $query = sprintf(
            'SELECT %s ' .
            'FROM ((SELECT `id`, `user` FROM `%s`) UNION ALL (SELECT 0 `id`, "Allgemein" `user`)) `u` %s ' .
            'ORDER BY `u`.`user`',
            implode(', ', $selects),
            $this->userTable->getTableName(),
            implode(' ', $joins),
        );

        try {
            foreach ($this->databaseStoreWrapper->getClient()->execute($query, $parameters)?->iterateRecords() ?? [] as $record) {
                yield array_map(
                    static fn (Value $value): float|int|string|null => $value->getValue(),
                    $record->getValues(),
                );
            }
        } catch (ClientException) {
            // Do nothing
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
}
