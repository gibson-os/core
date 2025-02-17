<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Role;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\Permission;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Value;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;

/**
 * @extends AbstractDatabaseStore<Role>
 */
class PermissionStore extends AbstractDatabaseStore
{
    private ?int $moduleId = null;

    private ?int $taskId = null;

    private ?int $actionId = null;

    public function __construct(
        #[GetTableName(Permission::class)]
        private readonly string $permissionTableName,
        #[GetTableName(Module::class)]
        private readonly string $moduleTableName,
        #[GetTableName(Task::class)]
        private readonly string $taskTableName,
        #[GetTableName(Action::class)]
        private readonly string $actionTableName,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Role::class;
    }

    protected function getAlias(): ?string
    {
        return 'r';
    }

    protected function getDefaultOrder(): array
    {
        return ['`r`.`name`' => OrderDirection::ASC];
    }

    protected function initQuery(): void
    {
        parent::initQuery();

        $selects = [
            'roleId' => '`r`.`id`',
            'roleName' => '`r`.`name`',
            'modulePermissionId' => '`upm`.`id`',
            'modulePermission' => '`upm`.`permission`',
            'moduleId' => '`m`.`id`',
            'moduleName' => '`m`.`name`',
        ];
        $parameters = [];

        if ($this->moduleId !== null) {
            $this->selectQuery
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upm',
                    '`r`.`id`=`upm`.`role_id` AND `m`.`id`=:moduleId AND `upm`.`task_id` IS NULL',
                    JoinType::LEFT,
                ))
                ->addJoin(new Join(
                    $this->getTable($this->moduleTableName),
                    'm',
                    '`upm`.`module_id`=`m`.`id`',
                    JoinType::LEFT,
                ))
            ;

            $parameters = ['moduleId' => $this->moduleId];
            $selects['taskId'] = 'NULL';
            $selects['taskName'] = 'NULL';
            $selects['actionId'] = 'NULL';
            $selects['actionName'] = 'NULL';
            $selects['id'] = '`upm`.`id`';
            $selects['permission'] = '`upm`.`permission`';
            $selects['parentId'] = 'NULL';
            $selects['parentPermission'] = (string) PermissionEnum::DENIED->value;
            $selects['taskPermissionId'] = 'NULL';
            $selects['taskPermission'] = 'NULL';
            $selects['actionPermissionId'] = 'NULL';
            $selects['actionPermission'] = 'NULL';
        }

        if ($this->taskId !== null) {
            $this->selectQuery
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upt',
                    '`r`.`id`=`upt`.`role_id` AND `upt`.`task_id`=`t`.`id` AND `upt`.`action_id` IS NULL',
                    JoinType::LEFT,
                ))
                ->addJoin(new Join(
                    $this->getTable($this->taskTableName),
                    't',
                    '`upt`.`task_id`=:taskId',
                    JoinType::LEFT,
                ))
                ->addJoin(new Join(
                    $this->getTable($this->moduleTableName),
                    'm',
                    '`m`.`id`=`t`.`module_id`',
                    JoinType::LEFT,
                ))
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upm',
                    '`r`.`id`=`upm`.`role_id` AND `upm`.`module_id`=`m`.`id` AND `upm`.`task_id` IS NULL',
                    JoinType::LEFT,
                ))
            ;

            $parameters = ['taskId' => $this->taskId];
            $selects['taskId'] = '`t`.`id`';
            $selects['taskName'] = '`t`.`name`';
            $selects['actionId'] = 'NULL';
            $selects['actionName'] = 'NULL';
            $selects['id'] = '`upt`.`id`';
            $selects['permission'] = '`upt`.`permission`';
            $selects['parentId'] = '`upm`.`id`';
            $selects['parentPermission'] = sprintf('IFNULL(`upm`.`permission`, %d)', PermissionEnum::DENIED->value);
            $selects['taskPermissionId'] = '`upt`.`id`';
            $selects['taskPermission'] = '`upt`.`permission`';
            $selects['actionPermissionId'] = 'NULL';
            $selects['actionPermission'] = 'NULL';
        }

        if ($this->actionId !== null) {
            $this->selectQuery
                ->addJoin(new Join(
                    $this->getTable($this->actionTableName),
                    'a',
                    '`a`.`id`=:actionId',
                    JoinType::LEFT,
                ))
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upa',
                    '`r`.`id`=`upa`.`role_id` AND `upa`.`action_id`=`a`.`id`',
                ))
                ->addJoin(new Join(
                    $this->getTable($this->taskTableName),
                    't',
                    '`t`.`id`=`a`.`task_id`',
                ))
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upt',
                    '`r`.`id`=`upt`.`role_id` AND `upt`.`task_id`=`t`.`id` AND `upt`.`action_id` IS NULL',
                ))
                ->addJoin(new Join(
                    $this->getTable($this->moduleTableName),
                    'm',
                    '`m`.`id`=`t`.`module_id`',
                ))
                ->addJoin(new Join(
                    $this->getTable($this->permissionTableName),
                    'upm',
                    '`r`.`id`=`upm`.`role_id` AND `upm`.`module_id`=`m`.`id` AND `upm`.`task_id` IS NULL',
                ))
            ;

            $parameters = ['actionId' => $this->actionId];
            $selects['taskId'] = '`t`.`id`';
            $selects['taskName'] = '`t`.`name`';
            $selects['actionId'] = '`a`.`id`';
            $selects['actionName'] = '`a`.`name`';
            $selects['id'] = '`upa`.`id`';
            $selects['permission'] = '`upa`.`permission`';
            $selects['parentId'] = 'IFNULL(`upt`.`id`, `upm`.`id`)';
            $selects['parentPermission'] = sprintf(
                'IFNULL(IFNULL(`upt`.`permission`, `upm`.`permission`), %d)',
                PermissionEnum::DENIED->value,
            );
            $selects['taskPermissionId'] = '`upt`.`id`';
            $selects['taskPermission'] = '`upt`.`permission`';
            $selects['actionPermissionId'] = '`upa`.`id`';
            $selects['actionPermission'] = '`upa`.`permission`';
        }

        $this->selectQuery
            ->setSelects($selects)
            ->addWhere(new Where('1', $parameters))
        ;
    }

    /**
     * @throws ClientException
     *
     * @return iterable<array>
     */
    protected function getModels(): iterable
    {
        $result = $this->getDatabaseStoreWrapper()->getClient()->execute($this->selectQuery);

        foreach ($result->iterateRecords() as $record) {
            yield array_map(
                static fn (Value $value): float|int|string|null => $value->getValue(),
                $record->getValues(),
            );
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
