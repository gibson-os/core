<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use JsonException;
use MDO\Exception\ClientException;
use ReflectionException;

class UserRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(int $id): User
    {
        return $this->fetchOne('`id`=?', [$id], User::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return User[]
     */
    public function findByName(string $name): array
    {
        return $this->fetchAll('`user` LIKE ?', [$name . '%'], User::class);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByUsername(string $username): User
    {
        return $this->fetchOne('`user`=?', [$username], User::class);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByUsernameAndPassword(string $username, string $passwordHash): User
    {
        return $this->fetchOne('`user`=? AND `password`=MD5(?)', [$username, $passwordHash], User::class);
    }

    /**
     * @throws ClientException
     */
    public function getCount(): int
    {
        return (int) $this->getAggregations(['count' => 'COUNT(`id`)'], User::class)->get('count')?->getValue() ?? 0;
    }
}
