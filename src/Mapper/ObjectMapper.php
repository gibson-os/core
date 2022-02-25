<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use GibsonOS\Core\Attribute\ObjectMapper as ObjectMapperAttribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class ObjectMapper implements ObjectMapperInterface
{
    public function __construct(
        private ServiceManager $serviceManagerService,
        private ReflectionManager $reflectionManager
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     */
    public function mapToObject(string $className, array $properties): object
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($className);
        $constructorParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            if (!array_key_exists($reflectionParameter->getName(), $properties)) {
                $constructorParameters[] = $this->reflectionManager->getDefaultValue($reflectionParameter);

                continue;
            }

            $property = $properties[$reflectionParameter->getName()];
            $constructorParameters[] = $this->mapValueToObject($reflectionParameter, $property);
            unset($properties[$reflectionParameter->getName()]);
        }

        $object = new $className(...$constructorParameters);
        $this->setObjectValues($object, $properties);

        return $object;
    }

    public function mapFromObject(object $object): array
    {
        return [];
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
                $this->mapValueToObject($reflectionClass->getMethod('set' . ucfirst($key))->getParameters()[0], $value)
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
        int|float|string|bool|array|object|null $values
    ): int|float|string|bool|array|object|null {
        $attribute = $this->reflectionManager->getAttribute($reflectionObject, ObjectMapperAttribute::class);

        if (is_object($values)) {
            if (enum_exists($values::class)) {
                return $values->value;
            }

            return $values;
        }

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
                        $objectClassName
                    ));
                }

                return array_map(
                    fn ($value) => $mapper->mapToObject($objectClassName, is_array($value)
                        ? $value
                        : [$reflectionObject->getName() => $value]),
                    $values
                );
            }

            $typeName = $this->reflectionManager->getNonBuiltinTypeName($reflectionObject);

            if (enum_exists($typeName)) {
                return empty($values) ? null : $typeName::from($values);
            }

            return $mapper->mapToObject(
                $objectClassName ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionObject),
                is_array($values) ? $values : [$reflectionObject->getName() => $values]
            );
        }

        if (
            $values === null &&
            $reflectionObject instanceof ReflectionParameter &&
            !$reflectionObject->allowsNull()
        ) {
            return $this->reflectionManager->getDefaultValue($reflectionObject);
        }

        return $values;
    }
}
