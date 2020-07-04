<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\AbstractRepository;

class DeviceRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getById(string $id): Device
    {
        $table = $this->getTable(Device::getTableName());
        $table->setWhere('`id`=' . $this->escape($id));

        if (!$table->select()) {
            $exception = new SelectError('Device not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new Device();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByToken(string $token): Device
    {
        $table = $this->getTable(Device::getTableName());
        $table->setWhere('`token`=' . $this->escape($token));

        if (!$table->select()) {
            $exception = new SelectError('Device not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new Device();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByCryptedToken(string $cryptedToken, string $salt, string $secret): Device
    {
        $table = $this->getTable(Device::getTableName());
        $table->setWhere(
            'MD5(CONCAT(`token`, ' . $this->escape($salt) . ', ' . $secret . ')=' . $this->escape($cryptedToken)
        );

        if (!$table->select()) {
            $exception = new SelectError('Device not found!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new Device();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
