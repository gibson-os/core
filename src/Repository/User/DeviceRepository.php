<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\AbstractRepository;

/**
 * @method Device fetchOne(string $where, array $parameters, string $modelClassName)
 */
class DeviceRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getById(string $id): Device
    {
        return $this->fetchOne('`id`=?', [$id], Device::class);
    }

    /**
     * @throws SelectError
     */
    public function getByToken(string $token): Device
    {
        return $this->fetchOne('`token`=?', [$token], Device::class);
    }

    /**
     * @throws SelectError
     */
    public function getByCryptedToken(string $cryptedToken, string $salt, string $secret): Device
    {
        return $this->fetchOne('MD5(CONCAT(`token`, ?, ?)=?', [$salt, $secret, $cryptedToken], Device::class);
    }
}
