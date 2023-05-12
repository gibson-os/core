<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Role;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Permission as UserPermission;

/**
 * @method Role        getRole()
 * @method Permission  setRole(Role $role)
 * @method Module      getModule()
 * @method Permission  setModule(Module $module)
 * @method Task|null   getTask()
 * @method Permission  setTask(?Task $task)
 * @method Action|null getAction()
 * @method Permission  setAction(?Action $action)
 */
#[Table]
#[Key(unique: true, columns: ['module_id', 'task_id', 'action_id', 'role_id'])]
class Permission extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $moduleId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $taskId = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $actionId = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $roleId;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $permission = UserPermission::DENIED;

    #[Constraint]
    protected Role $role;

    #[Constraint]
    protected Module $module;

    #[Constraint]
    protected ?Task $task;

    #[Constraint]
    protected ?Action $action;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Permission
    {
        $this->id = $id;

        return $this;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function setModuleId(int $moduleId): Permission
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function setTaskId(?int $taskId): Permission
    {
        $this->taskId = $taskId;

        return $this;
    }

    public function getActionId(): ?int
    {
        return $this->actionId;
    }

    public function setActionId(?int $actionId): Permission
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): Permission
    {
        $this->roleId = $roleId;

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
