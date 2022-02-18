<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class ReflectionManager
{
    private const GETTER_PREFIXES = ['get', 'is', 'has', 'should'];

    /**
     * @var array<class-string, ReflectionClass>
     */
    private array $classes = [];

    /**
     * @var array<class-string, ReflectionEnum>
     */
    private array $enums = [];

    /**
     * @param class-string|object $objectOrClass
     *
     * @throws ReflectionException
     */
    public function getReflectionClass(string|object $objectOrClass): ReflectionClass
    {
        $className = $objectOrClass;

        if (is_object($className)) {
            $className = $className::class;
        }

        if (!isset($this->classes[$className])) {
            $this->classes[$className] = new ReflectionClass($objectOrClass);
        }

        return $this->classes[$className];
    }

    /**
     * @param class-string|object $objectOrClass
     *
     * @throws ReflectionException
     */
    public function getReflectionEnum(string|object $objectOrClass): ReflectionEnum
    {
        $className = $objectOrClass;

        if (is_object($className)) {
            $className = $className::class;
        }

        if (!isset($this->enums[$className])) {
            $this->enums[$className] = new ReflectionEnum($objectOrClass);
        }

        return $this->enums[$className];
    }

    /**
     * @template T
     *
     * @param class-string<T> $attributeClassName
     *
     * @return T[]
     */
    public function getAttributes(
        ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject,
        string $attributeClassName,
        int $flags = 0
    ): array {
        $attributes = [];

        foreach ($reflectionObject->getAttributes($attributeClassName, $flags) as $attribute) {
            /** @var T $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            $attributes[] = $attributeInstance;
        }

        return $attributes;
    }

    /**
     * @template T
     *
     * @param class-string<T> $attributeClassName
     *
     * @return T|null
     */
    public function getAttribute(
        ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject,
        string $attributeClassName,
        int $flags = 0
    ): ?object {
        $attributes = $this->getAttributes($reflectionObject, $attributeClassName, $flags);

        if (count($attributes) === 1) {
            return reset($attributes);
        }

        return null;
    }

    /**
     * @param class-string $attributeClassName
     */
    public function hasAttribute(
        ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject,
        string $attributeClassName,
        int $flags = 0
    ): bool {
        return count($reflectionObject->getAttributes($attributeClassName, $flags)) > 0;
    }

    public function setProperty(
        ReflectionProperty $reflectionProperty,
        object $object,
        string|int|float|bool|null|array|object $value
    ): bool {
        $propertyName = $reflectionProperty->getName();
        $setter = 'set' . ucfirst($propertyName);

        if ($reflectionProperty->getDeclaringClass()->hasMethod($setter)) {
            $object->$setter($value);

            return true;
        }

        if ($reflectionProperty->isPublic()) {
            $object->$propertyName = $value;

            return true;
        }

        return false;
    }

    public function getProperty(
        ReflectionProperty $reflectionProperty,
        object $object
    ): string|int|float|bool|null|array|object {
        $propertyName = $reflectionProperty->getName();

        foreach (self::GETTER_PREFIXES as $getterPrefix) {
            $getter = $getterPrefix . lcfirst($propertyName);

            if ($reflectionProperty->getDeclaringClass()->hasMethod($getter)) {
                return $object->$getter();
            }
        }

        if ($reflectionProperty->isPublic()) {
            return $object->$propertyName;
        }

        throw new ReflectionException(sprintf(
            'Property "%s" of class "%s" has no getter or is not public!',
            $propertyName,
            $reflectionProperty->getDeclaringClass()->getName()
        ));
    }

    /**
     * @throws ReflectionException
     */
    public function getDefaultValue(ReflectionParameter $reflectionParameter): string|int|float|bool|null|array|object
    {
        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        if ($reflectionParameter->allowsNull()) {
            return null;
        }

        $reflectionClass = $reflectionParameter->getDeclaringClass();

        if (!$reflectionClass) {
            throw new ReflectionException(sprintf(
                'Parameter "%s" has no class!',
                $reflectionParameter->getName()
            ));
        }

        try {
            $reflectionProperty = $reflectionClass->getProperty($reflectionParameter->getName());

            if ($reflectionProperty->hasDefaultValue()) {
                return $reflectionProperty->getDefaultValue();
            }
        } catch (ReflectionException) {
        }

        throw new ReflectionException(sprintf(
            'Parameter "%s" of class "%s" has no default value!',
            $reflectionParameter->getName(),
            $reflectionClass->getName()
        ));
    }
}
