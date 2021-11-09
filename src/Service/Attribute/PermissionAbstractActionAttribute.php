<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;

class PermissionAbstractActionAttribute extends AbstractActionAttributeService
{
    public function __construct(
        private PermissionService $permissionService,
        private RequestService $requestService,
        private SessionService $sessionService
    ) {
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function preExecute(AttributeInterface $attribute, array $parameters): array
    {
        if (!$attribute instanceof CheckPermission) {
            return $parameters;
        }

        $requiredPermission = $attribute->getPermission();

        foreach ($attribute->getPermissionsByRequestValues() as $requestKey => $requestPermission) {
            try {
                $this->requestService->getRequestValue($requestKey);
                $requiredPermission = $requestPermission;
            } catch (RequestError) {
                // do nothing
            }
        }

        $permission = $this->permissionService->getPermission(
            $this->requestService->getModuleName(),
            $this->requestService->getTaskName(),
            $this->requestService->getActionName(),
            $this->sessionService->getUserId()
        );

        if ($this->permissionService->checkPermission($requiredPermission, $permission)) {
            $permissionParameter = $attribute->getPermissionParameter();

            if (!isset($parameters[$permissionParameter])) {
                $parameters[$permissionParameter] = $permission;
            }

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

        return [$attribute->getPermissionParameter()];
    }
}
