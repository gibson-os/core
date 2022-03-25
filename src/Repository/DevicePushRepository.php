<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Device;

class DevicePushRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function get(Device $device, Module $module, Task $task, Action $action, string $foreignId): DevicePush
    {
        return $this->fetchOne(
            '`device_id`=? AND `module`=? AND `task`=? AND `action`=? AND `foreign_id`=?',
            [$device->getId(), $module->getName(), $task->getName(), $action->getName(), $foreignId],
            DevicePush::class
        );
    }
}
