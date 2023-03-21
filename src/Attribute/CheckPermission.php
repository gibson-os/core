<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CheckPermission implements AttributeInterface
{
    public function __construct(
        private readonly int $permission,
        private readonly array $permissionsByRequestValues = [],
        private readonly string $permissionParameter = 'userPermission',
        private readonly string $userParameter = 'user',
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
