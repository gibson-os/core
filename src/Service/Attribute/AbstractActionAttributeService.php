<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Service\Response\ResponseInterface;
use ReflectionParameter;

abstract class AbstractActionAttributeService implements AttributeServiceInterface
{
    /**
     * @param ReflectionParameter[] $reflectionParameters
     */
    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        return $parameters;
    }

    public function postExecute(AttributeInterface $attribute, ResponseInterface $response): void
    {
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        return [];
    }

    /**
     * @param $name
     * @param ReflectionParameter[] $reflectionParameters
     */
    protected function getReflectionParameter($name, array $reflectionParameters): ?ReflectionParameter
    {
        foreach ($reflectionParameters as $reflectionParameter) {
            if ($name === $reflectionParameter->getName()) {
                return $reflectionParameter;
            }
        }

        return null;
    }
}
