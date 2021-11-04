<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\AbstractRepository;

/**
 * @method Device   fetchOne(string $where, array $parameters, string $modelClassName)
 * @method Device[] fetchAll(string $where, array $parameters, string $modelClassName, int $limit = null, int $offset = null, string $orderBy = null)
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

    /**
     * @throws SelectError
     *
     * @return Device[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->fetchAll('`user_id`=?', [$userId], Device::class);
    }

    public function deleteByIds(array $ids, int $userId = null): void
    {
        $table = $this->getTable(Device::getTableName());
        $table
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;

        $where = '`id` IN (' . $table->getParametersString($ids) . ')';

        if ($userId !== null) {
            $where .= ' AND `user_id`=?';
            $table->addWhereParameter($userId);
        }

        $table->setWhere($where);
    }
}
