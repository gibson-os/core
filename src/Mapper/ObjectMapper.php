<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use GibsonOS\Core\Exception\MapperException;
use ReflectionClass;
use ReflectionException;

class ObjectMapper
{
    /**
     * @template T
     *
     * @param class-string<T>      $className
     * @param array<string, mixed> $properties
     *
     * @throws MapperException
     * @throws ReflectionException
     *
     * @return T
     */
    public function map(string $className, array $properties): object
    {
        $reflectionClass = new ReflectionClass($className);
        $reflectionConstructor = $reflectionClass->getConstructor();
        $constructorParameters = [];

        if ($reflectionConstructor !== null) {
            foreach ($reflectionConstructor->getParameters() as $reflectionParameter) {
                if (!isset($properties[$reflectionParameter->getName()])) {
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

                $constructorParameters[] = $properties[$reflectionParameter->getName()];
                unset($properties[$reflectionParameter->getName()]);
            }
        }

        $object = new $className(...$constructorParameters);

        foreach ($properties as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (!method_exists($object, $setter)) {
                throw new MapperException(sprintf(
                    'Setter for property "%s" of class "%s" not found!',
                    $key,
                    $className
                ));
            }

            $object->$setter($value);
        }

        return $object;
    }
}
