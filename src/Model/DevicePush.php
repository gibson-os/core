<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\User\Device;

/**
 * @method Device     getDevice()
 * @method DevicePush setDevice(Device $device)
 * @method Module     getModuleModel()
 * @method DevicePush setModuleModel(Module $module)
 * @method Task       getTaskModel()
 * @method DevicePush setTaskModel(Task $task)
 * @method Action     getActionModel()
 * @method DevicePush setActionModel(Action $action)
 */
#[Table]
class DevicePush extends AbstractModel
{
    #[Column(length: 16, primary: true)]
    private string $deviceId;

    #[Column(length: 32, primary: true)]
    private string $module;

    #[Column(length: 32, primary: true)]
    private string $task;

    #[Column(length: 32, primary: true)]
    private ?string $action = null;

    #[Column(length: 512, primary: true)]
    private string $foreignId;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP, attributes: [Column::ATTRIBUTE_CURRENT_TIMESTAMP])]
    private \DateTimeInterface $modified;

    #[Constraint]
    protected Device $device;

    #[Constraint(parentColumn: 'name', ownColumn: 'module')]
    protected Module $moduleModel;

    #[Constraint(parentColumn: 'name', ownColumn: 'task')]
    protected Task $taskModel;

    #[Constraint(parentColumn: 'name', ownColumn: 'action')]
    protected Action $actionModel;

    public function __construct(\mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->modified = new \DateTimeImmutable();
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

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): DevicePush
    {
        $this->action = $action;

        return $this;
    }

    public function getForeignId(): string
    {
        return $this->foreignId;
    }

    public function setForeignId(string $foreignId): DevicePush
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    public function getModified(): \DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): DevicePush
    {
        $this->modified = $modified;

        return $this;
    }
}
