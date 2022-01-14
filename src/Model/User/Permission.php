<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\User;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;

/**
 * @method ?User getUser()
 */
#[Table]
class Permission extends AbstractModel
{
    public const INHERIT = 0; // 00000

    public const DENIED = 1;  // 00001

    public const READ = 2;    // 00010

    public const WRITE = 4;   // 00100

    public const DELETE = 8;  // 01000

    public const MANAGE = 16;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 32)]
    private string $module;

    #[Column(length: 32)]
    private ?string $task = null;

    #[Column(length: 32)]
    private ?string $action = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $userId = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $permission = self::DENIED;

    #[Constraint]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Permission
    {
        $this->id = $id;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): Permission
    {
        $this->module = $module;

        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(?string $task): Permission
    {
        $this->task = $task;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): Permission
    {
        $this->action = $action;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): Permission
    {
        $this->userId = $userId;

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

    public function setUser(?User $user): Permission
    {
        $this->user = $user;
        $this->setUserId($user?->getId());

        return $this;
    }
}
