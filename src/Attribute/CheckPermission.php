<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CheckPermission implements AttributeInterface
{
    /**
     * @param Permission[]                $permissions
     * @param array<string, Permission[]> $permissionsByRequestValues
     */
    public function __construct(
        private readonly array $permissions,
        private readonly array $permissionsByRequestValues = [],
        private readonly string $permissionParameter = 'userPermission',
        private readonly string $userParameter = 'permissionUser',
    ) {
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return array<string, Permission[]>
     */
    public function getPermissionsByRequestValues(): array
    {
        return $this->permissionsByRequestValues;
    }

    public function getAttributeServiceName(): string
    {
        return PermissionAttribute::class;
    }

    public function getPermissionParameter(): string
    {
        return $this->permissionParameter;
    }

    public function getUserParameter(): string
    {
        return $this->userParameter;
    }
}
