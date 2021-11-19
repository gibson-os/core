<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use JsonSerializable;
use mysqlDatabase;

class Setting extends AbstractModel implements JsonSerializable
{
    private int $userId = 0;

    private int $moduleId = 0;

    private string $key = '';

    private string $value = '';

    private User $user;

    private Module $module;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->user = new User();
    }

    public static function getTableName(): string
    {
        return 'setting';
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Setting
    {
        $this->userId = $userId;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Setting
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): Setting
    {
        $this->key = $key;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): Setting
    {
        $this->value = $value;

        return $this;
    }

    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    public function setUser(User $user): Setting
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }

    public function getModule(): Module
    {
        $this->loadForeignRecord($this->module, $this->getModuleId());

        return $this->module;
    }

    public function setModule(Module $module): Setting
    {
        $this->module = $module;
        $this->setModuleId($module->getId() ?? 0);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'key' => $this->getKey(),
            'value' => $this->getValue(),
            'userId' => $this->getUserId(),
            'moduleId' => $this->getModuleId(),
        ];
    }
}
