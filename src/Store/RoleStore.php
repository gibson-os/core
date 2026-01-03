<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Role;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Role>
 */
class RoleStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getModelClassName(): string
    {
        return Role::class;
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }
}
