<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\Setting;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use ReflectionException;
use ReflectionParameter;

class SettingAttribute extends AbstractActionAttributeService implements ServiceAttributeServiceInterface, AttributeServiceInterface
{
    public function __construct(
        private SettingRepository $settingRepository,
        private RequestService $requestService,
        private SessionService $sessionService,
    ) {
    }

    /**
     * @param ReflectionParameter[] $reflectionParameters
     *
     * @throws SelectError
     * @throws ReflectionException
     */
    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        return $this->getSetting($attribute, $parameters, $reflectionParameters);
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof Setting) {
            return [];
        }

        return [$this->getKey($attribute)];
    }

    private function getKey(Setting $attribute): string
    {
        return $attribute->getName() ?? lcfirst(implode(
            '',
            array_map(
                fn (string $part) => ucfirst(mb_strtolower($part)),
                explode('_', $attribute->getKey())
            )
        ));
    }

    /**
     * @throws ReflectionException
     * @throws SelectError
     */
    public function beforeConstruct(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        return $this->getSetting($attribute, $parameters, $reflectionParameters);
    }

    /**
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getSetting(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof Setting) {
            return $parameters;
        }

        $key = $this->getKey($attribute);

        try {
            $parameters[$key] = $this->settingRepository->getByKeyAndModuleName(
                $attribute->getModule() ?? $this->requestService->getModuleName(),
                $this->sessionService->getUserId() ?? 0,
                $attribute->getKey()
            );
        } catch (SelectError $exception) {
            $reflectionParameter = $this->getReflectionParameter($key, $reflectionParameters);

            if ($reflectionParameter !== null) {
                if (!$reflectionParameter->isOptional() && !$reflectionParameter->allowsNull()) {
                    throw $exception;
                }

                if ($reflectionParameter->isOptional()) {
                    $parameters[$key] = $reflectionParameter->getDefaultValue();
                }

                $parameters[$key] = null;
            }
        }

        return $parameters;
    }
}
