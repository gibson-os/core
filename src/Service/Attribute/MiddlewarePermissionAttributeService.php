<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\CheckMiddlewarePermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\RequestService;
use Override;

class MiddlewarePermissionAttributeService extends AbstractActionAttributeService
{
    public function __construct(
        private readonly PermissionAttribute $permissionAttribute,
        private readonly RequestService $requestService,
        #[GetSetting('middlewareSecret', 'core')]
        private readonly Setting $middlewareSecret,
    ) {
    }

    /**
     * @throws PermissionDenied
     * @throws LoginRequired
     * @throws SelectError
     */
    #[Override]
    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof CheckMiddlewarePermission) {
            return $parameters;
        }

        $parameters = $this->permissionAttribute->preExecute($attribute, $parameters, $reflectionParameters);

        try {
            $secret = $this->requestService->getHeader('X-GibsonOs-Secret');
        } catch (RequestError) {
            throw new PermissionDenied();
        }

        if ($secret !== $this->middlewareSecret->getValue()) {
            throw new PermissionDenied();
        }

        $parameters[$attribute->getSecretParameter()] = $secret;

        return $parameters;
    }

    #[Override]
    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof CheckMiddlewarePermission) {
            return [];
        }

        $usedParameters = $this->permissionAttribute->usedParameters($attribute);
        $usedParameters[] = $attribute->getSecretParameter();

        return $usedParameters;
    }
}
