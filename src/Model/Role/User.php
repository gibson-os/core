<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Role;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Role;
use GibsonOS\Core\Model\User as BaseUser;
use JsonSerializable;
use Override;

/**
 * @method User     setRole(Role $role)
 * @method Role     getRole()
 * @method User     setUser(BaseUser $user)
 * @method BaseUser getUser()
 */
#[Table]
#[Key(unique: true, columns: ['role_id', 'user_id'])]
class User extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $roleId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Constraint]
    protected Role $role;

    #[Constraint]
    protected BaseUser $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): User
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): User
    {
        $this->userId = $userId;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'roleId' => $this->roleId,
            'userId' => $this->getUserId(),
            'userName' => $this->getUser()->getUser(),
        ];
    }
}
