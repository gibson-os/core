<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Setting;

class SettingStore extends AbstractDatabaseStore
{
    private ?int $userId = null;

    private ?int $moduleId = null;

    protected function getModelClassName(): string
    {
        return Setting::class;
    }

    protected function getCountField(): string
    {
        return '*';
    }

    protected function getDefaultOrder(): string
    {
        return '`key`';
    }

    protected function setWheres(): void
    {
        if ($this->userId !== null) {
            $this->addWhere('`user_id`=? OR `user_id` IS NULL', [$this->userId]);
        }

        if ($this->moduleId !== null) {
            $this->addWhere('`module_id`=?', [$this->moduleId]);
        }
    }

    public function setUserId(?int $userId): SettingStore
    {
        $this->userId = $userId;

        return $this;
    }

    public function setModuleId(?int $moduleId): SettingStore
    {
        $this->moduleId = $moduleId;

        return $this;
    }
}
