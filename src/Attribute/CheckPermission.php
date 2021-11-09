<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\PermissionAbstractActionAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CheckPermission implements AttributeInterface
{
    public function __construct(
        private int $permission,
        private array $permissionsByRequestValues = [],
        private string $permissionParameter = 'userPermission'
    ) {
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function getPermissionsByRequestValues(): array
    {
        return $this->permissionsByRequestValues;
    }

    public function getAttributeServiceName(): string
    {
        return PermissionAbstractActionAttribute::class;
    }

    public function getPermissionParameter(): string
    {
        return $this->permissionParameter;
    }
}
