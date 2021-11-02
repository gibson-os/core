<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\User;

class UserStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return User::class;
    }

    protected function getDefaultOrder(): string
    {
        return '`user`';
    }
}
