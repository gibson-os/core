<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use DateTimeInterface;
use GibsonOS\Core\Attribute\ObjectMapper as ObjectMapperAttribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Override;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

class ObjectMapper implements ObjectMapperInterface
{
    public function __construct(
        private readonly ServiceManager $serviceManagerService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @throws ReflectionException
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     *
     * @return T
     */
    #[Override]
    public function mapToObject(string $className, array $properties): object
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($className);
        $constructorParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            if (!array_key_exists($reflectionParameter->getName(), $properties)) {
                $constructorParameters[] = $this->reflectionManager->getDefaultValue($reflectionParameter);

                continue;
            }

            $value = $properties[$reflectionParameter->getName()];
            $property = is_object($value) ? $value : $this->reflectionManager->castValue($reflectionParameter, $value);

            $constructorParameters[] = is_object($property)
                ? $property
                : $this->mapValueToObject($reflectionParameter, $property)
            ;
            unset($properties[$reflectionParameter->getName()]);
        }

        $object = new $className(...$constructorParameters);
        $this->setObjectValues($object, $properties);

        return $object;
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function setObjectValues(object $object, array $properties): object
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($object);

        foreach ($properties as $key => $value) {
            try {
                $reflectionProperty = $reflectionClass->getProperty($key);
            } catch (ReflectionException) {
                continue;
            }

            $this->reflectionManager->setProperty(
                $reflectionProperty,
                $object,
                $this->mapValueToObject($reflectionClass->getMethod('set' . ucfirst($key))->getParameters()[0], $value),
            );
        }

        return $object;
    }

    /**
     * @throws MapperException
     * @throws ReflectionException
     * @throws FactoryError
     * @throws JsonException
     */
    protected function mapValueToObject(
        ReflectionParameter|ReflectionProperty $reflectionObject,
        int|float|string|bool|array|object|null $values,
    ): int|float|string|bool|array|object|null {
        $attribute = $this->reflectionManager->getAttribute($reflectionObject, ObjectMapperAttribute::class);

        if ($attribute !== null || !$this->reflectionManager->isBuiltin($reflectionObject)) {
            $mapper = $this;
            $objectClassName = null;

            if ($attribute !== null) {
                $objectClassName = $attribute->getObjectClassName();
                $mapper = $this->serviceManagerService->get($attribute->getMapperClassName(), ObjectMapperInterface::class);
            }

            if ($values === null && $reflectionObject instanceof ReflectionParameter) {
                return $this->reflectionManager->getDefaultValue($reflectionObject);
            }

            if ($this->reflectionManager->getTypeName($reflectionObject) === 'array' && $objectClassName !== null) {
                if (is_string($values)) {
                    $values = JsonUtility::decode($values);
                }

                if (!is_array($values)) {
                    throw new MapperException(sprintf(
                        'Parameter for object "%s" is no array!',
                        $objectClassName,
                    ));
                }

                return array_map(
                    fn ($value) => is_object($value) ? $value : $mapper->mapToObject($objectClassName, is_array($value)
                        ? $value
                        : [$reflectionObject->getName() => $value]),
                    $values,
                );
            }

            $typeName = $this->reflectionManager->getNonBuiltinTypeName($reflectionObject);

            if (enum_exists($typeName)) {
                if (is_object($values) || is_array($values)) {
                    throw new ReflectionException(sprintf(
                        'Value for enum "%s" is an array or object!',
                        $typeName,
                    ));
                }

                if ($values === null || mb_strlen(trim((string) $values)) === 0) {
                    return null;
                }

                try {
                    return constant(sprintf('%s::%s', $typeName, (string) $values));
                } catch (Throwable) {
                    $enumReflection = $this->reflectionManager->getReflectionEnum($typeName);

                    return $typeName::from(match ((string) $enumReflection->getBackingType()) {
                        'string' => (string) $values,
                        'int' => (int) $values,
                        'float' => (float) $values,
                    });
                }
            }

            if (is_subclass_of($typeName, DateTimeInterface::class)) {
                return new $typeName($values);
            }

            return $mapper->mapToObject(
                $objectClassName ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionObject),
                is_array($values) ? $values : [$reflectionObject->getName() => $values],
            );
        }

        if (
            $values === null
            && $reflectionObject instanceof ReflectionParameter
            && !$reflectionObject->allowsNull()
        ) {
            return $this->reflectionManager->getDefaultValue($reflectionObject);
        }

        return $values;
    }
}
