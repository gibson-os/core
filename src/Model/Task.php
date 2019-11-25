<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;

class Task extends AbstractModel
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
     * @var Module
     */
    private $module;

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'task';
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
     * @return Task
     */
    public function setId(int $id): Task
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
     * @return Task
     */
    public function setName(string $name): Task
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
     * @return Task
     */
    public function setModuleId(int $moduleId): Task
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return Module
     */
    public function getModule(): Module
    {
        $this->loadForeignRecord($this->module, $this->getModuleId());

        return $this->module;
    }

    /**
     * @param Module $module
     *
     * @return Task
     */
    public function setModule(Module $module): Task
    {
        $this->module = $module;

        return $this;
    }
}
