<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use JsonSerializable;

class Setting extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private ?int $userId = null;

    private int $moduleId = 0;

    private string $key = '';

    private string $value = '';

    private ?User $user = null;

    private Module $module;

    public static function getTableName(): string
    {
        return 'setting';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Setting
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): Setting
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

    public function getUser(): ?User
    {
        $userId = $this->getUserId();

        if ($userId === null) {
            $this->user = null;

            return null;
        }

        if ($this->user === null) {
            $this->user = new User();
        }

        $this->loadForeignRecord($this->user, $userId);

        return $this->user;
    }

    public function setUser(?User $user): Setting
    {
        $this->user = $user;
        $this->setUserId($user?->getId());

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
