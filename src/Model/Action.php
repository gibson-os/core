<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use JsonSerializable;
use mysqlDatabase;

class Action extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private string $name = '';

    private int $moduleId = 0;

    private int $taskId = 0;

    private Module $module;

    private Task $task;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->module = new Module();
        $this->task = new Task();
    }

    public static function getTableName(): string
    {
        return 'action';
    }

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

    /**
     * @throws DateTimeError
     */
    public function getModule(): Module
    {
        $this->loadForeignRecord($this->module, $this->getModuleId());

        return $this->module;
    }

    public function setModule(Module $module): Action
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function getTask(): Task
    {
        $this->loadForeignRecord($this->task, $this->getTaskId());

        return $this->task;
    }

    public function setTask(Task $task): Action
    {
        $this->task = $task;

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
