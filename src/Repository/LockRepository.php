<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Lock;

class LockRepository extends AbstractRepository
{
    public function getByName(string $name): Lock
    {
        $table = $this->getTable(Lock::getTableName());
        $table->setWhere('`name`=' . $this->escape($name));
        $table->select();

        if (!$table->select()) {
            $exception = new SelectError('Lock not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new Lock();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
