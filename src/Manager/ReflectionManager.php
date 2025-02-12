<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

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
     * @var array<class-string, string[]>
     */
    private array $docMethods = [];

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
    public function getAttributes(ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject, string $attributeClassName, int $flags = 0): array
    {
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
    public function getAttribute(ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject, string $attributeClassName, int $flags = 0): ?object
    {
        $attributes = $this->getAttributes($reflectionObject, $attributeClassName, $flags);

        if (count($attributes) === 1) {
            return reset($attributes);
        }

        if ($reflectionObject instanceof ReflectionParameter) {
            $reflectionProperty = $this->getPropertyByParameter($reflectionObject);

            if ($reflectionProperty instanceof ReflectionProperty) {
                return $this->getAttribute($reflectionProperty, $attributeClassName, $flags);
            }
        }

        return null;
    }

    /**
     * @param class-string $attributeClassName
     */
    public function hasAttribute(ReflectionClass|ReflectionParameter|ReflectionProperty|ReflectionMethod|ReflectionClassConstant $reflectionObject, string $attributeClassName, int $flags = 0): bool
    {
        return $reflectionObject->getAttributes($attributeClassName, $flags) !== [];
    }

    public function setProperty(ReflectionProperty $reflectionProperty, object $object, string|int|float|bool|array|object|null $value): bool
    {
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

    public function getProperty(ReflectionProperty $reflectionProperty, object $object): string|int|float|bool|array|object|null
    {
        $propertyName = $reflectionProperty->getName();

        foreach (self::GETTER_PREFIXES as $getterPrefix) {
            $getter = $getterPrefix . ucfirst($propertyName);

            if ($reflectionProperty->getDeclaringClass()->hasMethod($getter)) {
                return $object->$getter();
            }

            if ($this->hasDocMethod($reflectionProperty->getDeclaringClass(), $getter)) {
                return $object->$getter();
            }
        }

        if ($reflectionProperty->isPublic()) {
            return $object->$propertyName;
        }

        throw new ReflectionException(sprintf('Property "%s" of class "%s" has no getter or is not public!', $propertyName, $reflectionProperty->getDeclaringClass()->getName()));
    }

    /**
     * @throws ReflectionException
     */
    public function getDefaultValue(ReflectionParameter $reflectionParameter): string|int|float|bool|array|object|null
    {
        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        if ($reflectionParameter->allowsNull()) {
            return null;
        }

        $reflectionProperty = $this->getPropertyByParameter($reflectionParameter);

        if (
            $reflectionProperty instanceof ReflectionProperty
            && $reflectionProperty->hasDefaultValue()
        ) {
            return $reflectionProperty->getDefaultValue();
        }

        throw new ReflectionException(sprintf('Parameter "%s" of class "%s" has no default value!', $reflectionParameter->getName(), $reflectionParameter->getDeclaringClass()?->getName() ?? 'null'));
    }

    /**
     * @throws ReflectionException
     */
    private function getPropertyByParameter(ReflectionParameter $reflectionParameter): ?ReflectionProperty
    {
        $reflectionClass = $reflectionParameter->getDeclaringClass();

        if (!$reflectionClass) {
            throw new ReflectionException(sprintf('Parameter "%s" has no class!', $reflectionParameter->getName()));
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ((string) $reflectionProperty->getType() !== (string) $reflectionParameter->getType()) {
                continue;
            }

            return $reflectionProperty;
        }

        return null;
    }

    public function getTypeName(ReflectionProperty|ReflectionParameter $reflectionObject): ?string
    {
        $type = $reflectionObject->getType();

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        return null;
    }

    /**
     * @throws ReflectionException
     *
     * @return class-string
     */
    public function getNonBuiltinTypeName(ReflectionProperty|ReflectionParameter $reflectionObject): string
    {
        $typeName = $this->getTypeName($reflectionObject);

        if ($typeName === null) {
            throw new ReflectionException(sprintf('Type for "%s" does not exists!', $reflectionObject->getName()));
        }

        if ($this->isBuiltin($reflectionObject)) {
            throw new ReflectionException(sprintf('Type "%s" for "%s" is build in!', $typeName, $reflectionObject->getName()));
        }

        if (!class_exists($typeName)) {
            throw new ReflectionException(sprintf('Class "%s" for "%s" does not exists!', $typeName, $reflectionObject->getName()));
        }

        return $typeName;
    }

    public function isBuiltin(ReflectionProperty|ReflectionParameter $reflectionObject): bool
    {
        $type = $reflectionObject->getType();

        if ($type instanceof ReflectionNamedType) {
            return $type->isBuiltin();
        }

        return false;
    }

    public function allowsNull(ReflectionProperty|ReflectionParameter $reflectionObject): bool
    {
        return $reflectionObject->getType()?->allowsNull() ?? false;
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function castValue(ReflectionProperty|ReflectionParameter $reflectionObject, int|float|bool|string|array|null $value): int|float|bool|string|array|object|null
    {
        if (!$this->isBuiltin($reflectionObject)) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $typeName = $this->getTypeName($reflectionObject);

        if ($typeName !== null && class_exists($typeName) && enum_exists($typeName)) {
            if (is_array($value)) {
                throw new ReflectionException(sprintf('Enum %s does not allow arrays!', $typeName));
            }

            try {
                return constant(sprintf('%s::%s', $typeName, (string) $value));
            } catch (Throwable) {
                $reflectionEnum = $this->getReflectionEnum($typeName);

                return $typeName::from(match ((string) $reflectionEnum->getBackingType()) {
                    'string' => (string) $value,
                    'int' => (int) $value,
                    'float' => (float) $value,
                });
            }
        }

        return match ($typeName) {
            'int' => is_numeric($value) ? (int) $value : null,
            'float' => is_numeric($value) ? (float) $value : null,
            'string' => is_array($value) ? JsonUtility::encode($value) : (string) $value,
            'array' => is_array($value) ? $value : (array) JsonUtility::decode((string) $value),
            'bool' => !is_array($value) && (mb_strtolower((string) $value) === 'true'
                || (is_numeric((string) $value) && ((int) $value))
                || (is_bool($value) && $value)),
            default => throw new ReflectionException(sprintf('Type "%s" of %s "%s" for "%s%s" is not allowed!', $this->getTypeName($reflectionObject) ?? 'null', $reflectionObject instanceof ReflectionParameter ? 'parameter' : 'property', $reflectionObject->getName(), $reflectionObject->getDeclaringClass()?->getName() ?? 'null', $reflectionObject instanceof ReflectionParameter ? '::' . $reflectionObject->getDeclaringFunction()->getName() : '')),
        };
    }

    public function hasDocMethod(ReflectionClass $reflectionClass, string $methodName): bool
    {
        return in_array($methodName, $this->getDocMethods($reflectionClass));
    }

    public function getDocMethods(ReflectionClass $reflectionClass): array
    {
        $className = $reflectionClass->getName();

        if (isset($this->docMethods[$className])) {
            return $this->docMethods[$className];
        }

        $this->docMethods[$className] = [];

        foreach (explode("\n", $reflectionClass->getDocComment() ?: '') as $line) {
            if (!str_contains($line, '@method')) {
                continue;
            }

            preg_match('/@method.*\s(.*)\(.*\)/', $line, $matches);

            $this->docMethods[$className][] = $matches[1];
        }

        return $this->docMethods[$className];
    }

    public function __serialize(): array
    {
        return [];
    }
}
