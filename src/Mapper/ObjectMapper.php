<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use GibsonOS\Core\Attribute\ObjectMapper as ObjectMapperAttribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\JsonUtility;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class ObjectMapper implements ObjectMapperInterface
{
    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws ReflectionException
     * @throws FactoryError
     * @throws MapperException
     */
    public function mapToObject(string $className, array $properties): object
    {
        $reflectionClass = new ReflectionClass($className);
        $constructorParameters = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $reflectionParameter) {
            if (!array_key_exists($reflectionParameter->getName(), $properties)) {
                if (!$reflectionParameter->isDefaultValueAvailable()) {
                    throw new MapperException(sprintf(
                        'Value for constructor parameter "%s" of class "%s" is missing!',
                        $reflectionParameter->getName(),
                        $className
                    ));
                }

                $constructorParameters[] = $reflectionParameter->getDefaultValue();

                continue;
            }

            $property = $properties[$reflectionParameter->getName()];
            $constructorParameters[] = $this->mapValueToObject($reflectionParameter, $property);
            unset($properties[$reflectionParameter->getName()]);
        }

        $object = new $className(...$constructorParameters);

        foreach ($properties as $key => $value) {
            $setter = 'set' . ucfirst($key);

            try {
                $reflectionMethod = $reflectionClass->getMethod($setter);
            } catch (ReflectionException) {
                throw new MapperException(sprintf(
                    'Setter for property "%s" of class "%s" not found!',
                    $key,
                    $className
                ));
            }

            if (count($reflectionMethod->getParameters()) === 0) {
                throw new MapperException(sprintf(
                    'Setter for property "%s" of class "%s" has nor parameters!',
                    $key,
                    $className
                ));
            }

            $object->$setter($this->mapValueToObject($reflectionMethod->getParameters()[0], $value));
        }

        return $object;
    }

    public function mapFromObject(object $object): array
    {
        return [];
    }

    /**
     * @throws MapperException
     * @throws ReflectionException
     * @throws FactoryError
     */
    private function mapValueToObject(
        ReflectionParameter $reflectionParameter,
        int|float|string|bool|array|object|null $values
    ): int|float|string|bool|array|object|null {
        $attributes = $reflectionParameter->getAttributes(ObjectMapperAttribute::class);
        /** @psalm-suppress UndefinedMethod */
        $parameterTypeName = $reflectionParameter->getType()?->getName();

        /** @psalm-suppress UndefinedMethod */
        if (count($attributes) === 1 || !$reflectionParameter->getType()?->isBuiltin()) {
            $mapper = $this;
            $objectClassName = null;

            if (count($attributes) === 1) {
                /** @var ObjectMapperAttribute $attribute */
                $attribute = $attributes[0]->newInstance();
                $objectClassName = $attribute->getObjectClassName();
                $mapper = $this->serviceManagerService->get($attribute->getMapperClassName(), ObjectMapperInterface::class);
            }

            if ($parameterTypeName === 'array' && $objectClassName !== null) {
                if (is_string($values)) {
                    $values = JsonUtility::decode($values);
                }

                return array_map(
                    fn ($value) => $mapper->mapToObject($objectClassName, is_array($value)
                        ? $value
                        : [$reflectionParameter->getName() => $value]),
                    $values
                );
            }

            if (!is_array($values)) {
                $values = [$reflectionParameter->getName() => $values];
            }

            return $mapper->mapToObject(
                $objectClassName ?? $parameterTypeName,
                $values
            );
        }

        return $values;
    }
}
