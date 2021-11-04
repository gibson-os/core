<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;

/**
 * @method User fetchOne(string $where, array $parameters, string $modelClassName)
 */
class UserRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getById(int $id): User
    {
        return $this->fetchOne('`id`=?', [$id], User::class);
    }

    /**
     * @throws SelectError
     */
    public function getByUsernameAndPassword(string $username, string $passwordHash): User
    {
        return $this->fetchOne('`user`=? AND `password`=MD5(?)', [$username, $passwordHash], User::class);
    }
}
