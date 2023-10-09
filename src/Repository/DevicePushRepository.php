<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\User\Device;
use JsonException;
use MDO\Exception\ClientException;
use ReflectionException;

class DevicePushRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByDevice(Device $device, string $module, string $task, string $action, string $foreignId): DevicePush
    {
        return $this->fetchOne(
            '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=? AND `device_id`=?',
            [$module, $task, $action, $foreignId, $device->getId()],
            DevicePush::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return DevicePush[]
     */
    public function getAllByAction(string $module, string $task, string $action, string $foreignId): array
    {
        return $this->fetchAll(
            '`module`=? AND `task`=? AND `action`=? AND `foreign_id`=?',
            [$module, $task, $action, $foreignId],
            DevicePush::class,
        );
    }
}
