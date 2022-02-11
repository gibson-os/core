<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\RequestService;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class ObjectMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(private ObjectMapper $objectMapper, private RequestService $requestService)
    {
    }

    /**
     * @throws MapperException
     * @throws ReflectionException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetObject) {
            return null;
        }

        /** @psalm-suppress UndefinedMethod */
        $objectClassName = $reflectionParameter->getType()?->getName();

        if ($objectClassName === null) {
            return null;
        }

        $reflectionClass = $reflectionParameter->getDeclaringClass();

        if ($reflectionClass === null) {
            throw new MapperException('Reflect class not found!');
        }

        $objectParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $requestKey = $attribute->getMapping()[$parameterName] ?? $parameterName;

            try {
                $objectParameters[$parameterName] = $this->requestService->getRequestValue($requestKey);
            } catch (RequestError) {
            }
        }

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (mb_strpos($reflectionMethod->getName(), 'set') !== 0) {
                continue;
            }

            $parameterName = ucfirst(mb_substr($reflectionMethod->getName(), 3));
            $requestKey = $attribute->getMapping()[$parameterName] ?? $parameterName;

            try {
                $objectParameters[$parameterName] = $this->requestService->getRequestValue($requestKey);
            } catch (RequestError) {
            }
        }

        return $this->objectMapper->mapToObject($objectClassName, $objectParameters);
    }
}
