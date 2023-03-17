<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use GibsonOS\Core\Service\Attribute\MiddlewarePermissionAttributeService;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CheckMiddlewarePermission extends CheckPermission
{
    public function __construct(
        int $permission,
        array $permissionsByRequestValues = [],
        string $permissionParameter = 'userPermission',
        private readonly string $secretParameter = 'secret',
    ) {
        parent::__construct($permission, $permissionsByRequestValues, $permissionParameter);
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
