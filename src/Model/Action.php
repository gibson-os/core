<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Enum\HttpMethod;
use JsonSerializable;
use Override;

/**
 * @method Module getModule()
 * @method Action setModule(Module $module)
 * @method Task   getTask()
 * @method Action setTask(Task $task)
 */
#[Table]
#[Key(unique: true, columns: ['name', 'method', 'task_id', 'module_id'])]
class Action extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 32)]
    private string $name;

    #[Column]
    private HttpMethod $method;

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

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    public function setMethod(HttpMethod $method): Action
    {
        $this->method = $method;

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

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'method' => $this->getMethod()->value,
            'moduleId' => $this->getModuleId(),
            'taskId' => $this->getTaskId(),
        ];
    }

    #[Override]
    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
