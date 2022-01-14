<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Action;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;

#[Table]
class Permission extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $actionId;

    #[Column(type: Column::TYPE_TINYINT, attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $permission;

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
