<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Role;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class UserStore extends AbstractDatabaseStore
{
    private Role $role;

    public function __construct(
        #[GetTableName(User::class)] private readonly string $userTableName,
        #[GetTableName(Role\User::class)] private readonly string $roleUserTableName,
        \mysqlDatabase $database = null,
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return User::class;
    }

    public function setRole(Role $role): UserStore
    {
        $this->role = $role;

        return $this;
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table->appendJoin($this->roleUserTableName, sprintf(
            '`%s`.`id`=`%s`.`user_id`',
            $this->userTableName,
            $this->roleUserTableName
        ));
    }

    protected function setWheres(): void
    {
        $this->addWhere(sprintf('`%s`.`role_id`=?', $this->roleUserTableName), [$this->role->getId() ?? 0]);
    }
}
