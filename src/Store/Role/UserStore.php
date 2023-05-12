<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Role;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\User;
use GibsonOS\Core\Model\User as BaseUser;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use mysqlDatabase;

/**
 * @extends AbstractDatabaseStore<User>
 */
class UserStore extends AbstractDatabaseStore
{
    private Role $role;

    public function __construct(
        #[GetTableName(BaseUser::class)] private readonly string $userTableName,
        #[GetTableName(User::class)] private readonly string $roleUserTableName,
        mysqlDatabase $database = null,
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

    protected function getDefaultOrder(): string
    {
        return sprintf('`%s`.`user`', $this->userTableName);
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table
            ->appendJoin($this->userTableName, sprintf(
                '`%s`.`id`=`%s`.`user_id`',
                $this->userTableName,
                $this->roleUserTableName
            ))
            ->setSelectString(sprintf(
                '`%s`.`id` `id`, ' .
                '`%s`.`role_id` `roleId`, ' .
                '`%s`.`id` `userId`, ' .
                '`%s`.`user` `userName`, ' .
                '`%s`.`password` `userPassword`, ' .
                '`%s`.`host` `userHost`, ' .
                '`%s`.`ip` `userIp`, ' .
                '`%s`.`added` `userAdded`, ' .
                '`%s`.`last_login` `userLastLogin`',
                $this->roleUserTableName,
                $this->roleUserTableName,
                $this->userTableName,
                $this->userTableName,
                $this->userTableName,
                $this->userTableName,
                $this->userTableName,
                $this->userTableName,
                $this->userTableName,
            ))
        ;
    }

    protected function setWheres(): void
    {
        $this->addWhere(sprintf('`%s`.`role_id`=?', $this->roleUserTableName), [$this->role->getId() ?? 0]);
    }

    protected function getModel(): User
    {
        $record = $this->table->getSelectedRecord();

        return (new User())
            ->setId((int) $record['id'])
            ->setRoleId((int) $record['roleId'])
            ->setUser(
                (new BaseUser())
                    ->setId((int) $record['userId'])
                    ->setUser($record['userName'])
                    ->setPassword($record['userPassword'])
                    ->setHost($record['userHost'])
                    ->setIp($record['userIp'])
                    ->setAdded(new DateTimeImmutable($record['userAdded']))
                    ->setLastLogin(
                        $record['userLastLogin'] === null
                        ? null
                        : new DateTimeImmutable($record['userLastLogin'])
                    )
            )
        ;
    }
}
