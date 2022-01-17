<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Action;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Action;

/**
 * @method Action     getAction()
 * @method Permission setAction(Action $action)
 */
#[Table]
class Permission extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $actionId;

    #[Column(type: Column::TYPE_TINYINT, attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $permission;

    #[Constraint]
    protected Action $action;

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
