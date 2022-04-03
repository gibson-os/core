<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\StatusCode;
use JsonException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class ObjectMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        protected ObjectMapper $objectMapper,
        protected RequestService $requestService,
        protected ReflectionManager $reflectionManager
    ) {
    }

    /**
     * @throws MapperException
     * @throws ReflectionException
     * @throws JsonException
     * @throws FactoryError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): ?object
    {
        if (!$attribute instanceof GetObject) {
            return null;
        }

        $objectClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);

        return $this->objectMapper->mapToObject(
            $objectClassName,
            $this->getObjectParameters($attribute, $objectClassName, $parameters)
        );
    }

    /**
     * @param class-string $objectClassName
     *
     * @throws MapperException
     * @throws ReflectionException
     */
    protected function getObjectParameters(GetObject $attribute, string $objectClassName, array $parameters): array
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($objectClassName);
        $objectParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $requestKey = $this->getMappingKey($attribute, $reflectionParameter);
            $objectParameters[$parameterName] = $parameters[$requestKey]
                ?? $this->getParameterFromRequest($reflectionParameter, $requestKey)
            ;
        }

        return array_merge($objectParameters, $this->getSetterParameters($attribute, $objectClassName, $parameters));
    }

    /**
     * @param class-string $objectClassName
     *
     * @throws MapperException
     * @throws ReflectionException
     */
    protected function getSetterParameters(GetObject $attribute, string $objectClassName, array $parameters): array
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($objectClassName);
        $setterParameters = [];

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (mb_strpos($reflectionMethod->getName(), 'set') !== 0) {
                continue;
            }

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterName = $reflectionParameter->getName();
                $requestKey = $this->getMappingKey($attribute, $reflectionParameter);

                try {
                    $this->requestService->getRequestValue($requestKey);
                    $setterParameters[$parameterName] = $parameters[$requestKey]
                        ?? $this->getParameterFromRequest($reflectionParameter, $requestKey)
                    ;
                } catch (RequestError) {
                }
            }
        }

        return $setterParameters;
    }

    protected function getMappingKey(
        GetObject $attribute,
        ReflectionParameter|ReflectionProperty $reflectionObject
    ): string {
        $parameterName = $reflectionObject->getName();

        return $attribute->getMapping()[$parameterName] ?? $parameterName;
    }

    /**
     * @throws MapperException
     */
    public function getParameterFromRequest(
        ReflectionParameter $reflectionParameter,
        string $requestKey = null
    ): string|int|float|bool|null|array|object {
        try {
            $value = $this->requestService->getRequestValue($requestKey ?? $reflectionParameter->getName());
        } catch (RequestError) {
            try {
                return $this->reflectionManager->getDefaultValue($reflectionParameter);
            } catch (ReflectionException $e) {
                try {
                    $reflectionProperty = $reflectionParameter->getDeclaringClass()?->getProperty($reflectionParameter->getName());

                    if ($reflectionProperty === null || !$reflectionProperty->hasDefaultValue()) {
                        throw new MapperException(sprintf(
                            'Parameter "%s" is not in request!',
                            $requestKey ?? $reflectionParameter->getName()
                        ), 0, $e);
                    }

                    return $reflectionProperty->getDefaultValue();
                } catch (ReflectionException) {
                    throw new MapperException($e->getMessage(), StatusCode::BAD_REQUEST, $e);
                }
            }
        }

        if ($value === null || $value === '') {
            try {
                return $this->reflectionManager->getDefaultValue($reflectionParameter);
            } catch (ReflectionException $e) {
                throw new MapperException($e->getMessage());
            }
        }

        try {
            return $this->reflectionManager->castValue($reflectionParameter, $value);
        } catch (JsonException|ReflectionException $e) {
            throw new MapperException($e->getMessage());
        }
    }
}
