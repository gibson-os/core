<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class ReflectionManager
{
    /**
     * @var array<class-string, ReflectionClass>
     */
    private array $classes = [];

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
}
