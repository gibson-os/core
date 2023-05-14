<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Service\Attribute\MiddlewarePermissionAttributeService;

#[Attribute(Attribute::TARGET_METHOD)]
class CheckMiddlewarePermission extends CheckPermission
{
    /**
     * @param Permission[]                $permissions
     * @param array<string, Permission[]> $permissionsByRequestValues
     */
    public function __construct(
        array $permissions,
        array $permissionsByRequestValues = [],
        string $permissionParameter = 'userPermission',
        private readonly string $secretParameter = 'secret',
    ) {
        parent::__construct($permissions, $permissionsByRequestValues, $permissionParameter);
    }

    public function getAttributeServiceName(): string
    {
        return MiddlewarePermissionAttributeService::class;
    }

    public function getSecretParameter(): string
    {
        return $this->secretParameter;
    }
}
