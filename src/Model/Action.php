<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use mysqlDatabase;

class Action extends AbstractModel
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $moduleId = 0;

    /**
     * @var int
     */
    private $taskId = 0;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var Task
     */
    private $task;

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
     * @throws SelectError
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
     * @throws SelectError
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
}
