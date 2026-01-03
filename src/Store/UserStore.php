<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\User;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<User>
 */
class UserStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getModelClassName(): string
    {
        return User::class;
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`user`' => OrderDirection::ASC];
    }
}
