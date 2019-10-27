<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use mysqlDatabase;

class Action extends AbstractModel
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var int
     */
    private $taskId;

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

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'action';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Action
     */
    public function setId(int $id): Action
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Action
     */
    public function setName(string $name): Action
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    /**
     * @param int $moduleId
     *
     * @return Action
     */
    public function setModuleId(int $moduleId): Action
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaskId(): int
    {
        return $this->taskId;
    }

    /**
     * @param int $taskId
     *
     * @return Action
     */
    public function setTaskId(int $taskId): Action
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * @param Module $module
     *
     * @return Action
     */
    public function setModule(Module $module): Action
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return Task
     */
    public function getTask(): Task
    {
        return $this->task;
    }

    /**
     * @param Task $task
     *
     * @return Action
     */
    public function setTask(Task $task): Action
    {
        $this->task = $task;

        return $this;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Action
     */
    public function loadModule(): Action
    {
        $this->loadForeignRecord($this->getModule(), $this->getModuleId());

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return Action
     */
    public function loadTask(): Action
    {
        $this->loadForeignRecord($this->getTask(), $this->getTaskId());

        return $this;
    }
}
