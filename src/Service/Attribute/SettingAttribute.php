<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use Override;
use ReflectionException;
use ReflectionParameter;

class SettingAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly RequestService $requestService,
        private readonly SessionService $sessionService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    #[Override]
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter,
    ): string|int|float|bool|array|object|null {
        if (!$attribute instanceof GetSetting) {
            return null;
        }

        try {
            return $this->settingRepository->getByKeyAndModuleName(
                $attribute->getModule() ?? $this->requestService->getModuleName(),
                $this->sessionService->getUserId(),
                $attribute->getKey(),
            );
        } catch (SelectError) {
            return $this->reflectionManager->getDefaultValue($reflectionParameter);
        }
    }
}
