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
use GibsonOS\Core\Utility\JsonUtility;
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
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     */
    protected function getObjectParameters(GetObject $attribute, string $objectClassName, array $parameters): array
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($objectClassName);
        $objectParameters = [];
        $constructorProperties = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $requestKey = $this->getRequestKey($attribute, $reflectionParameter);
            $objectParameters[$parameterName] = $parameters[$requestKey]
                ?? $this->getParameterFromRequest($reflectionParameter, $requestKey)
            ;
            $constructorProperties[] = $parameterName;
        }

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (
                mb_strpos($reflectionMethod->getName(), 'set') !== 0 &&
                !in_array(lcfirst(mb_substr($reflectionMethod->getName(), 3)), $constructorProperties)
            ) {
                continue;
            }

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterName = $reflectionParameter->getName();
                $requestKey = $this->getRequestKey($attribute, $reflectionParameter);

                try {
                    $this->requestService->getRequestValue($requestKey);
                    $objectParameters[$parameterName] = $parameters[$requestKey]
                        ?? $this->getParameterFromRequest($reflectionParameter, $requestKey)
                    ;
                } catch (RequestError|MapperException) {
                }
            }
        }

        return $objectParameters;
    }

    protected function getRequestKey(
        GetObject $attribute,
        ReflectionParameter|ReflectionProperty $reflectionObject
    ): string {
        $parameterName = $reflectionObject->getName();

        return $attribute->getMapping()[$parameterName] ?? $parameterName;
    }

    /**
     * @throws JsonException
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

        return match ($this->reflectionManager->getTypeName($reflectionParameter)) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => $value === 'true' || ((int) $value),
            'string' => (string) $value,
            'array' => !is_array($value) ? (array) JsonUtility::decode($value) : $value,
            default => throw new MapperException(sprintf(
                'Type %s of parameter %s for %s::%s is not allowed!',
                $this->reflectionManager->getTypeName($reflectionParameter) ?? 'null',
                $reflectionParameter->getName(),
                $reflectionParameter->getDeclaringClass() === null ? '' : $reflectionParameter->getDeclaringClass()->getName(),
                $reflectionParameter->getDeclaringFunction()->getName()
            ))
        };
    }
}
