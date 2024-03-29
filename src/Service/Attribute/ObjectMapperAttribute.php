<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\RequestService;
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
        protected ReflectionManager $reflectionManager,
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

        try {
            $properties = $this->getObjectParameters($attribute, $objectClassName, $parameters);
        } catch (MapperException) {
            $properties = [];
        }

        try {
            return $this->objectMapper->mapToObject($objectClassName, $properties);
        } catch (ReflectionException $exception) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * @param class-string $objectClassName
     *
     * @throws MapperException
     * @throws ReflectionException
     */
    public function getObjectParameters(GetObject $attribute, string $objectClassName, array $parameters): array
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

    public function getMappingKey(
        GetObject $attribute,
        ReflectionParameter|ReflectionProperty $reflectionObject,
    ): string {
        $parameterName = $reflectionObject->getName();

        return $attribute->getMapping()[$parameterName] ?? $parameterName;
    }

    /**
     * @throws MapperException
     */
    public function getParameterFromRequest(
        ReflectionParameter $reflectionParameter,
        ?string $requestKey = null,
    ): string|int|float|bool|array|object|null {
        try {
            $value = $this->requestService->getRequestValue($requestKey ?? $reflectionParameter->getName());
        } catch (RequestError) {
            try {
                return $this->reflectionManager->getDefaultValue($reflectionParameter);
            } catch (ReflectionException $exception) {
                try {
                    $reflectionProperty = $reflectionParameter->getDeclaringClass()?->getProperty($reflectionParameter->getName());
                } catch (ReflectionException) {
                    $reflectionProperty = null;
                }

                try {
                    if ($reflectionProperty === null || !$reflectionProperty->hasDefaultValue()) {
                        throw new MapperException(sprintf(
                            'Parameter "%s" is not in request!',
                            $requestKey ?? $reflectionParameter->getName(),
                        ), 0, $exception);
                    }

                    return $reflectionProperty->getDefaultValue();
                } catch (ReflectionException $exception2) {
                    throw new MapperException($exception2->getMessage(), HttpStatusCode::BAD_REQUEST->value, $exception);
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
