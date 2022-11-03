<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Service\EnvService;

class EnvAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(private EnvService $envService)
    {
    }

    public function replace(AttributeInterface $attribute, array $parameters, \ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetEnv) {
            return null;
        }

        $reflectionParameterType = $reflectionParameter->getType();

        if ($reflectionParameterType instanceof \ReflectionNamedType) {
            $parameterType = ucfirst($reflectionParameterType->getName());

            return $this->envService->{'get' . $parameterType}($attribute->getKey());
        }

        return null;
    }
}
