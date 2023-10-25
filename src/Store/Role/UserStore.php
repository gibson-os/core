<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Role;

use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Role\User;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Enum\OrderDirection;

/**
 * @extends AbstractDatabaseStore<User>
 */
class UserStore extends AbstractDatabaseStore
{
    private Role $role;

    public function __construct(DatabaseStoreWrapper $databaseStoreWrapper)
    {
        parent::__construct($databaseStoreWrapper);
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

    protected function getAlias(): string
    {
        return 'ru';
    }

    protected function getDefaultOrder(): array
    {
        return ['`u`.`user`' => OrderDirection::ASC];
    }

    protected function setWheres(): void
    {
        $this->addWhere('`ru`.`role_id`=?', [$this->role->getId() ?? 0]);
    }

    protected function getExtends(): array
    {
        return [new ChildrenMapping('user', 'user', 'u')];
    }
}
