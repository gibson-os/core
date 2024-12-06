<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use JsonException;
use MDO\Exception\RecordException;
use ReflectionException;

class PermissionAttribute extends AbstractActionAttributeService
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly RequestService $requestService,
        private readonly SessionService $sessionService,
    ) {
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof CheckPermission) {
            return $parameters;
        }

        $requiredPermission = $this->getPermissionSum($attribute->getPermissions());

        foreach ($attribute->getPermissionsByRequestValues() as $requestKey => $requestPermissions) {
            try {
                $this->requestService->getRequestValue($requestKey);
                $requiredPermission = $this->getPermissionSum($requestPermissions);
            } catch (RequestError) {
                // do nothing
            }
        }

        $permission = $this->permissionService->getPermission(
            $this->requestService->getModuleName(),
            $this->requestService->getTaskName(),
            $this->requestService->getActionName(),
            HttpMethod::from($this->requestService->getMethod()),
            $this->sessionService->getUserId(),
        );

        if ($this->permissionService->checkPermission($requiredPermission, $permission)) {
            $parameters[$attribute->getPermissionParameter()] = $permission;
            $parameters[$attribute->getUserParameter()] = $this->sessionService->getUser();

            return $parameters;
        }

        if ($this->sessionService->isLogin()) {
            throw new PermissionDenied();
        }

        throw new LoginRequired();
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof CheckPermission) {
            return [];
        }

        return [$attribute->getPermissionParameter(), $attribute->getUserParameter()];
    }

    /**
     * @param Permission[] $permissions
     */
    private function getPermissionSum(array $permissions): int
    {
        return array_sum(array_map(
            static fn (Permission $permission): int => $permission->value,
            $permissions,
        ));
    }
}
