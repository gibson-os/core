<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Attribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\Attribute\ServiceAttributeServiceInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

class ServiceManagerService
{
    /**
     * @var array<class-string, object>
     */
    private array $services = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $interfaces = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $abstracts = [];

    private AttributeService $attributeService;

    public function __construct()
    {
        $this->services[self::class] = $this;
        $attributeService = new AttributeService($this);
        $this->services[AttributeService::class] = $attributeService;
        $this->attributeService = $attributeService;
    }

    /**
     * @template T
     *
     * @param class-string<T>   $classname
     * @param class-string|null $instanceOf
     *
     * @throws FactoryError
     *
     * @return T|object
     */
    public function get(string $classname, string $instanceOf = null): object
    {
        if (mb_strpos($classname, '\\') === 0) {
            $classname = substr($classname, 1);
        }

        if (
            !class_exists($classname) &&
            !interface_exists($classname)
        ) {
            throw new FactoryError(sprintf('Class or interface %s does not exists', $classname));
        }

        if (isset($this->services[$classname])) {
            $this->checkInstanceOf($this->services[$classname], $instanceOf);

            return $this->services[$classname];
        }

        $class = $this->getByCreate($classname);

        if ($class instanceof $classname) {
            $this->services[$classname] = $class;

            $this->checkInstanceOf($class, $instanceOf);

            return $class;
        }

        throw new FactoryError(sprintf('Class %s could not be created', $classname));
    }

    /**
     * @param class-string|null $className
     *
     * @throws FactoryError
     */
    private function checkInstanceOf(object $class, string $className = null): void
    {
        if (
            $className !== null &&
            !is_subclass_of($class, $className)
        ) {
            throw new FactoryError(sprintf('%d is no instance of %d', $class::class, $className));
        }
    }

    /**
     * @template T
     *
     * @param class-string<T> $classname
     *
     * @throws FactoryError
     *
     * @return T|object
     */
    private function getByCreate(string $classname): object
    {
        $reflection = $this->getReflectionsClass($classname);

        if ($reflection->isInterface()) {
            if (isset($this->interfaces[$classname])) {
                return $this->get($this->interfaces[$classname]);
            }

            throw new FactoryError(sprintf(
                'Class %s is an Interface',
                $classname
            ));
        }

        if ($reflection->isAbstract()) {
            if (isset($this->abstracts[$classname])) {
                return $this->get($this->abstracts[$classname]);
            }

            throw new FactoryError(sprintf(
                'Class %s is an Abstract class',
                $classname
            ));
        }

        $constructor = $reflection->getConstructor();
        $parameters = [];

        if ($constructor instanceof ReflectionMethod) {
            $attributes = $this->attributeService->getMethodAttributes($constructor);
            $parameters = $this->beforeConstructAttributes($attributes, $parameters, $constructor->getParameters());

            foreach ($constructor->getParameters() as $parameter) {
                $name = $parameter->getName();

                if (array_key_exists($name, $parameters)) {
                    continue;
                }

                $parameterType = $parameter->getType();

                if (
                    $parameterType instanceof ReflectionNamedType &&
                    !$parameterType->isBuiltin()
                ) {
                    $parameters[$name] = $this->get($parameterType->getName());

                    continue;
                }

                try {
                    $parameters[$name] = $parameter->getDefaultValue();
                } catch (ReflectionException) {
                    throw new FactoryError(sprintf(
                        'Parameter %s of Class %s is no Class',
                        $parameter->getName(),
                        $classname
                    ));
                }
            }

            $parameters = $this->transformParameters($constructor, $parameters);
        }

        return new $classname(...$parameters);
    }

    /**
     * @template T
     *
     * @param class-string<T> $classname
     *
     * @throws FactoryError
     *
     * @return ReflectionClass<T>
     */
    private function getReflectionsClass(string $classname): ReflectionClass
    {
        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            return new ReflectionClass($classname);
        } catch (ReflectionException $e) {
            throw new FactoryError(sprintf('Reflection class for %s could not be created', $classname), 0, $e);
        }
    }

    /**
     * @param class-string $name
     */
    public function setService(string $name, object $class): void
    {
        $this->services[$name] = $class;
    }

    /**
     * @param class-string $interfaceName
     * @param class-string $className
     */
    public function setInterface(string $interfaceName, string $className): void
    {
        $this->interfaces[$interfaceName] = $className;
    }

    /**
     * @param class-string $abstractName
     * @param class-string $className
     */
    public function setAbstract(string $abstractName, string $className): void
    {
        $this->abstracts[$abstractName] = $className;
    }

    /**
     * @throws FactoryError
     */
    private function transformParameters(ReflectionMethod $reflectionMethod, array $parameters): array
    {
        $newParameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (!array_key_exists($parameter->getName(), $parameters)) {
                continue;
            }

            $newParameters[] = $parameters[$parameter->getName()];
        }

        if (count($newParameters) < count($parameters)) {
            throw new FactoryError(sprintf(
                'Following parameters not in method signature: $%s',
                implode(', $', array_diff($parameters, $newParameters))
            ));
        }

        return $newParameters;
    }

    /**
     * @param Attribute[] $attributes
     */
    private function beforeConstructAttributes(array $attributes, array $parameters, array $reflectionParameters): array
    {
        foreach ($attributes as $attribute) {
            $attributeService = $attribute->getService();

            if (!$attributeService instanceof ServiceAttributeServiceInterface) {
                continue;
            }

            $parameters = $attributeService->beforeConstruct($attribute->getAttribute(), $parameters, $reflectionParameters);
        }

        return $parameters;
    }
}
