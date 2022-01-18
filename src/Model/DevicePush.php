<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\User\Device;
use mysqlDatabase;

/**
 * @method Module     getModuleModel()
 * @method DevicePush setModuleModel(Module $module)
 * @method Task       getTaskModel()
 * @method DevicePush setTaskModel(Task $task)
 */
#[Table]
#[Key(columns: ['registered', 'modified'])]
class DevicePush extends AbstractModel
{
    #[Column(length: 16, primary: true)]
    private string $deviceId;

    #[Column(length: 32, primary: true)]
    private string $module;

    #[Column(length: 32, primary: true)]
    private string $task;

    #[Column]
    private bool $registered = false;

    #[Column(default: Column::DEFAULT_CURRENT_TIMESTAMP, attributes: [Column::ATTRIBUTE_CURRENT_TIMESTAMP])]
    private DateTimeInterface $modified;

    #[Constraint]
    protected Device $device;

    #[Constraint(parentColumn: 'name', ownColumn: 'module')]
    protected Module $moduleModel;

    #[Constraint(parentColumn: 'name', ownColumn: 'task')]
    protected Task $taskModel;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->modified = new DateTimeImmutable();
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): DevicePush
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): DevicePush
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function setTask(string $task): DevicePush
    {
        $this->task = $task;

        return $this;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }

    public function setRegistered(bool $registered): DevicePush
    {
        $this->registered = $registered;

        return $this;
    }

    public function getModified(): DateTimeImmutable|DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(DateTimeImmutable|DateTimeInterface $modified): DevicePush
    {
        $this->modified = $modified;

        return $this;
    }
}
