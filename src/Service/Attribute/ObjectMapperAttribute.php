<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use JsonException;
use ReflectionClass;
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
     * @throws JsonException
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

        $reflectionClass = new ReflectionClass($objectClassName);
        $objectParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $requestKey = $attribute->getMapping()[$parameterName] ?? $parameterName;
            $objectParameters[$parameterName] = $this->getParameterFromRequest($reflectionParameter, $requestKey);
        }

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (mb_strpos($reflectionMethod->getName(), 'set') !== 0) {
                continue;
            }

            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterName = $reflectionParameter->getName();
                $requestKey = $attribute->getMapping()[$parameterName] ?? $parameterName;
                $objectParameters[$parameterName] = $this->getParameterFromRequest($reflectionParameter, $requestKey);
            }
        }

        return $this->objectMapper->mapToObject($objectClassName, $objectParameters);
    }

    /**
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function getParameterFromRequest(
        ReflectionParameter $reflectionParameter,
        string $requestKey = null
    ): array|bool|float|int|string|null {
        try {
            $value = $this->requestService->getRequestValue($requestKey ?? $reflectionParameter->getName());
        } catch (RequestError $e) {
            if ($reflectionParameter->isOptional()) {
                try {
                    return $reflectionParameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    throw new MapperException($e->getMessage(), StatusCode::BAD_REQUEST, $e);
                }
            }

            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw new MapperException(sprintf(
                'Parameter %s is not in request!',
                $requestKey ?? $reflectionParameter->getName()
            ), 0, $e);
        }

        if ($value === null || $value === '') {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            if ($reflectionParameter->isOptional()) {
                return $reflectionParameter->getDefaultValue();
            }

            throw new MapperException(sprintf(
                'Parameter %s doesnt allows null!',
                $reflectionParameter->getName()
            ));
        }

        /** @psalm-suppress UndefinedMethod */
        return match ($reflectionParameter->getType()?->getName()) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => $value === 'true' || ((int) $value),
            'string' => (string) $value,
            'array' => !is_array($value) ? (array) JsonUtility::decode($value) : $value,
            default => throw new MapperException(sprintf(
                'Type %s of parameter %s for %s::%s is not allowed!',
                (string) $reflectionParameter->getType(),
                $reflectionParameter->getName(),
                $reflectionParameter->getDeclaringClass() === null ? '' : $reflectionParameter->getDeclaringClass()->getName(),
                $reflectionParameter->getDeclaringFunction()->getName()
            ))
        };
    }
}
