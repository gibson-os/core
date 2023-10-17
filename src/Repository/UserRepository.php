<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class UserRepository extends AbstractRepository
{
    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getById(int $id): User
    {
        return $this->fetchOne('`id`=?', [$id], User::class);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RecordException
     * @throws ClientException
     *
     * @return User[]
     */
    public function findByName(string $name): array
    {
        return $this->fetchAll('`user` LIKE ?', [$name . '%'], User::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByUsername(string $username): User
    {
        return $this->fetchOne('`user`=?', [$username], User::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getByUsernameAndPassword(string $username, string $passwordHash): User
    {
        return $this->fetchOne('`user`=? AND `password`=MD5(?)', [$username, $passwordHash], User::class);
    }

    /**
     * @throws ClientException
     * @throws SelectError
     * @throws RecordException
     */
    public function getCount(): int
    {
        return (int) $this->getAggregations(['count' => 'COUNT(`id`)'], User::class)->get('count')->getValue();
    }
}
