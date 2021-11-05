<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Exception\DateTimeError;
use JsonSerializable;
use mysqlDatabase;

class Task extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private string $name = '';

    private int $moduleId = 0;

    private Module $module;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->module = new Module();
    }

    public static function getTableName(): string
    {
        return 'task';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Task
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Task
    {
        $this->name = $name;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Task
    {
        $this->moduleId = $moduleId;

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

    public function setModule(Module $module): Task
    {
        $this->module = $module;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'text' => $this->getName(),
        ];
    }
}
