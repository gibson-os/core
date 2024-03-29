<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\EnvService;
use ReflectionNamedType;
use ReflectionParameter;

class EnvAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(private readonly EnvService $envService)
    {
    }

    /**
     * @throws GetError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetEnv) {
            return null;
        }

        $reflectionParameterType = $reflectionParameter->getType();

        if ($reflectionParameterType instanceof ReflectionNamedType) {
            $parameterType = ucfirst($reflectionParameterType->getName());

            try {
                return $this->envService->{'get' . $parameterType}($attribute->getKey());
            } catch (GetError $exception) {
                if ($reflectionParameterType->allowsNull()) {
                    return null;
                }

                throw $exception;
            }
        }

        return null;
    }
}
