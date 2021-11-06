<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Action;

use GibsonOS\Core\Model\AbstractModel;

class Permission extends AbstractModel
{
    private int $actionId;

    private int $permission;

    public static function getTableName(): string
    {
        return 'action_permission';
    }

    public function getActionId(): int
    {
        return $this->actionId;
    }

    public function setActionId(int $actionId): Permission
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function setPermission(int $permission): Permission
    {
        $this->permission = $permission;

        return $this;
    }
}
