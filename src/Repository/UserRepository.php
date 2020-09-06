<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;

class UserRepository extends AbstractRepository
{
    public function getById(int $id): User
    {
        $table = $this->getTable(User::getTableName());
        $table->setWhere('`id`=' . $id);
        $table->setLimit(1);

        if (!$table->select()) {
            $exception = new SelectError('User not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new User();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByUsernameAndPassword(string $username, string $passwordHash): User
    {
        $table = $this->getTable(User::getTableName());
        $table->setWhere(
            '`user`=' . $this->escape($username) . ' AND ' .
            '`password`=MD5(' . $this->escape($passwordHash) . ')'
        );

        if (!$table->select()) {
            $exception = new SelectError('User not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new User();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
