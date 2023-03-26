<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\Attribute\ParameterAttributeInterface;
use GibsonOS\Core\Service\AttributeService;
use GibsonOS\Core\Service\DirService;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

class ServiceManager
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

    private ReflectionManager $reflectionManager;

    public function __construct()
    {
        $this->services[self::class] = $this;

        $this->reflectionManager = new ReflectionManager();
        $this->services[ReflectionManager::class] = $this->reflectionManager;

        $this->attributeService = new AttributeService($this, $this->reflectionManager);
        $this->services[AttributeService::class] = $this->attributeService;
    }

    /**
     * @template T
     *
     * @param class-string<T>      $classname
     * @param class-string<T>|null $instanceOf
     *
     * @throws FactoryError
     *
     * @return T
     */
    public function get(string $classname, string $instanceOf = null): object
    {
        if (\mb_strpos($classname, '\\') === 0) {
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
            /** @var T $class */
            $class = $this->services[$classname];

            return $class;
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
     * @template T
     *
     * @param class-string<T>|null $instanceOf
     *
     * @throws FactoryError
     * @throws GetError
     *
     * @return array<T|object>
     */
    public function getAll(string $dir, string $instanceOf = null): array
    {
        $classNames = $this->getClassNames($dir);
        $classes = [];

        foreach ($classNames as $className) {
            try {
                $classes[] = $this->get($className, $instanceOf);
            } catch (FactoryError) {
            }
        }

        return $classes;
    }

    /**
     * @throws FactoryError
     * @throws GetError
     *
     * @return class-string[]
     */
    public function getClassNames(string $dir): array
    {
        $dirService = $this->get(DirService::class);
        $classNames = [];

        foreach ($dirService->getFiles($dir) as $file) {
            if (is_dir($file)) {
                $classNames = array_merge($classNames, $this->getClassNames($file));

                continue;
            }

            try {
                $classNames[] = $this->getNamespaceByPath($file);
            } catch (FactoryError) {
            }
        }

        return $classNames;
    }

    /**
     * @throws FactoryError
     *
     * @return class-string
     */
    public function getNamespaceByPath(string $path): string
    {
        $dirService = $this->get(DirService::class);
        $pathParts = explode(DIRECTORY_SEPARATOR, $dirService->removeEndSlash($path));

        if (!is_file($path)) {
            throw new FactoryError(sprintf('"%s" is no file!', $path));
        }

        $handle = fopen($path, 'r');

        if (!is_resource($handle)) {
            throw new FactoryError(sprintf('"%s" can not be opened!', $path));
        }

        while (is_resource($handle) && ($line = fgets($handle)) !== false) {
            if (!preg_match('/((?<!#)\s|^)namespace\s+(.*);/', $line, $hits)) {
                continue;
            }

            fclose($handle);

            /** @var class-string $className */
            $className = $hits[2] . '\\' . str_replace('.php', '', array_pop($pathParts));

            return $className;
        }

        if (is_resource($handle)) {
            fclose($handle);
        }

        throw new FactoryError(sprintf('"%s" namespace not found!', $path));
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
    public function create(string $classname, array $parameters = [], string $instanceOf = null): object
    {
        $class = $this->getByCreate($classname, $parameters);

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
            throw new FactoryError(sprintf('%s is no instance of %s', $class::class, $className));
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
    private function getByCreate(string $classname, array $parameters = []): object
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

        if ($constructor instanceof ReflectionMethod) {
            foreach ($constructor->getParameters() as $reflectionParameter) {
                $name = $reflectionParameter->getName();

                if (array_key_exists($name, $parameters)) {
                    continue;
                }

                $attributes = $this->attributeService->getAttributes($reflectionParameter);

                if (count($attributes)) {
                    foreach ($attributes as $attribute) {
                        /** @var ParameterAttributeInterface $attributeService */
                        $attributeService = $attribute->getService();
                        $parameters[$name] = $attributeService->replace(
                            $attribute->getAttribute(),
                            $parameters,
                            $reflectionParameter
                        );
                    }

                    continue;
                }

                $parameterType = $reflectionParameter->getType();

                if (
                    $parameterType instanceof ReflectionNamedType &&
                    !$parameterType->isBuiltin()
                ) {
                    $parameters[$name] = $this->get($parameterType->getName());

                    continue;
                }

                try {
                    $parameters[$name] = $this->reflectionManager->getDefaultValue($reflectionParameter);
                } catch (ReflectionException) {
                    throw new FactoryError(sprintf(
                        'Parameter %s of Class %s is no Class',
                        $reflectionParameter->getName(),
                        $classname
                    ));
                }
            }

            $parameters = $this->transformParameters($constructor, $parameters);
        }

        return new $classname(...$parameters);
    }

    /**
     * @param class-string $classname
     *
     * @throws FactoryError
     */
    private function getReflectionsClass(string $classname): ReflectionClass
    {
        try {
            return $this->reflectionManager->getReflectionClass($classname);
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
}
