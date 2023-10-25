<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\User;
use MDO\Enum\OrderDirection;

/**
 * @extends AbstractDatabaseStore<User>
 */
class UserStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return User::class;
    }

    protected function getDefaultOrder(): array
    {
        return ['`user`' => OrderDirection::ASC];
    }
}
