<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use ReflectionException;
use ReflectionParameter;

class SettingAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private SettingRepository $settingRepository,
        private RequestService $requestService,
        private SessionService $sessionService,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws SelectError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetSetting) {
            return null;
        }

        try {
            return $this->settingRepository->getByKeyAndModuleName(
                $attribute->getModule() ?? $this->requestService->getModuleName(),
                $this->sessionService->getUserId(),
                $attribute->getKey()
            );
        } catch (SelectError $exception) {
            if (!$reflectionParameter->isOptional() && !$reflectionParameter->allowsNull()) {
                throw $exception;
            }

            if ($reflectionParameter->isOptional()) {
                return $reflectionParameter->getDefaultValue();
            }

            return null;
        }
    }
}
