<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\Env;
use GibsonOS\Core\Service\EnvService;
use ReflectionNamedType;

class EnvAttribute extends AbstractActionAttributeService
{
    public function __construct(private EnvService $envService)
    {
    }

    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof Env) {
            return $parameters;
        }

        $key = $this->getKey($attribute);
        $reflectionParameter = $this->getReflectionParameter($key, $reflectionParameters);

        if ($reflectionParameter === null) {
            return $parameters;
        }

        $reflectionParameterType = $reflectionParameter->getType();

        if ($reflectionParameterType instanceof ReflectionNamedType) {
            $parameterType = ucfirst($reflectionParameterType->getName());
            $parameters[$key] = $this->envService->{'get' . $parameterType}($attribute->getKey());
        }

        return $parameters;
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof Env) {
            return [];
        }

        return [$this->getKey($attribute)];
    }

    private function getKey(Env $attribute): string
    {
        return $attribute->getName() ?? lcfirst(implode(
            '',
            array_map(
                fn (string $part) => ucfirst(mb_strtolower($part)),
                explode('_', $attribute->getKey())
            )
        ));
    }
}
