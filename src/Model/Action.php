<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use JsonSerializable;

/**
 * @method Module getModule()
 * @method Task   getTask()
 */
#[Table]
class Action extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 32)]
    private string $name;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $moduleId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $taskId;

    #[Constraint]
    protected Module $module;

    #[Constraint]
    protected Task $task;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Action
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Action
    {
        $this->name = $name;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Action
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function setTaskId(int $taskId): Action
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function setModule(Module $module): Action
    {
        $this->module = $module;
        $this->setModuleId($module->getId() ?? 0);

        return $this;
    }

    public function setTask(Task $task): Action
    {
        $this->task = $task;
        $this->setTaskId($task->getId() ?? 0);

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'text' => $this->getName(),
            'moduleId' => $this->getModuleId(),
        ];
    }
}
