<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\Role\Permission;
use GibsonOS\Core\Model\Role\User as RoleUser;

/**
 * @method Permission[] getPermissions()
 * @method Role         setPermissions(Permission[] $permissions)
 * @method Role         addPermissions(Permission[] $permissions)
 * @method RoleUser[]   getUsers()
 * @method Role         setUsers(RoleUser[] $users)
 * @method Role         addUsers(RoleUser[] $users)
 */
#[Table]
class Role extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    #[Key(true)]
    private string $name;

    /**
     * @var Permission[]
     */
    #[Constraint('role', Permission::class)]
    protected array $permissions;

    /**
     * @var RoleUser[]
     */
    #[Constraint('role', Permission::class)]
    protected array $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Role
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Role
    {
        $this->name = $name;

        return $this;
    }
}
