<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;

class PermissionAttribute implements AttributeServiceInterface
{
    public function __construct(
        private PermissionService $permissionService,
        private RequestService $requestService,
        private SessionService $sessionService
    ) {
    }

    /**
     * @param AttributeInterface $attribute
     * @throws LoginRequired
     * @throws PermissionDenied
     * @return bool
     */
    public function evaluateAttribute(AttributeInterface $attribute): bool
    {
        if (!$attribute instanceof CheckPermission) {
            return false;
        }

        $permission = $attribute->getPermission();

        foreach ($attribute->getPermissionsByRequestValues() as $requestKey => $requestPermission) {
            try {
                $this->requestService->getRequestValue($requestKey);
                $permission = $requestPermission;
            } catch (RequestError) {
                // do nothing
            }
        }

        try {
            $hasPermission = $this->permissionService->hasPermission(
                $permission,
                $this->requestService->getModuleName(),
                $this->requestService->getTaskName(),
                $this->requestService->getActionName(),
                $this->sessionService->getUserId()
            );
        } catch (SelectError) {
            throw new PermissionDenied();
        }

        if ($hasPermission) {
            return true;
        }

        if ($this->sessionService->isLogin()) {
            throw new PermissionDenied();
        }

        throw new LoginRequired();
    }
}