<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Role;

/**
 * @extends AbstractDatabaseStore<Role>
 */
class RoleStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Role::class;
    }

    protected function getDefaultOrder(): string
    {
        return '`name`';
    }
}
