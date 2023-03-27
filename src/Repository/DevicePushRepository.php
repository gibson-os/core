<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\User\Device;

class DevicePushRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByDevice(Device $device, string $module, string $task, string $action, string $foreignId): DevicePush
    {
        return $this->fetchOne(
            '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=? AND `device_id`=?',
            [$module, $task, $action, $foreignId, $device->getId()],
            DevicePush::class
        );
    }

    /**
     * @throws SelectError
     *
     * @return DevicePush[]
     */
    public function getAllByAction(string $module, string $task, string $action, string $foreignId): array
    {
        return $this->fetchAll(
            '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=?',
            [$module, $task, $action, $foreignId],
            DevicePush::class
        );
    }
}
