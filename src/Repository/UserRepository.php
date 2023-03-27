<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;

class UserRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(User::class)] private readonly string $userTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): User
    {
        return $this->fetchOne('`id`=?', [$id], User::class);
    }

    /**
     * @throws SelectError
     *
     * @return User[]
     */
    public function findByName(string $name): array
    {
        $where = '`user` LIKE ?';
        $parameters = [$name . '%'];

        return $this->fetchAll($where, $parameters, User::class);
    }

    /**
     * @throws SelectError
     */
    public function getByUsername(string $username): User
    {
        return $this->fetchOne('`user`=?', [$username], User::class);
    }

    /**
     * @throws SelectError
     */
    public function getByUsernameAndPassword(string $username, string $passwordHash): User
    {
        return $this->fetchOne('`user`=? AND `password`=MD5(?)', [$username, $passwordHash], User::class);
    }

    public function getCount(): int
    {
        $table = $this->getTable($this->userTableName);
        $table->selectPrepared(false, 'COUNT(`id`)');

        return (int) $table->connection->fetchResult();
    }
}
