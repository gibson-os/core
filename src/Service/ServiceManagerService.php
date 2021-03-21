<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Factory\FactoryInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ServiceManagerService
{
    /**
     * @var object[]
     */
    private array $services = [];

    /**
     * @var string[]
     */
    private array $interfaces = [];

    /**
     * @var string[]
     */
    private array $abstracts = [];

    public function __construct()
    {
        $this->services[self::class] = $this;
    }

    /**
     * @throws FactoryError
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

        $class = $this->getByFactory($classname);

        if (!empty($class) && get_class($class) === $classname) {
            return $this->checkInstanceOf($class, $instanceOf);
        }

        if (isset($this->services[$classname])) {
            return $this->checkInstanceOf($this->services[$classname], $instanceOf);
        }

        $class = $this->getByCreate($classname);

        if ($class instanceof $classname) {
            $this->services[$classname] = $class;

            return $this->checkInstanceOf($class, $instanceOf);
        }

        throw new FactoryError(sprintf('Class %s could not be created', $classname));
    }

    private function checkInstanceOf(object $class, string $className = null): object
    {
        if (
            $className !== null &&
            !$class instanceof $className
        ) {
            throw new FactoryError(sprintf('%d is no instanceof of %d', get_class($class), $className));
        }

        return $class;
    }

    /**
     * @throws FactoryError
     */
    private function getByFactory(string $classname): ?object
    {
        $factoryName = mb_substr($classname, 0, -7) . 'Factory';
        $factoryName = str_replace('\\Service\\', '\\Factory\\', $factoryName);

        if (
            !class_exists($factoryName) ||
            $factoryName === $classname
        ) {
            return null;
        }

        /** @var FactoryInterface $factoryName */
        $class = $factoryName::create();

        if (get_class($class) !== $classname) {
            throw new FactoryError(sprintf('Factory not found for %s', $classname));
        }

        return $class;
    }

    /**
     * @throws FactoryError
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
            foreach ($constructor->getParameters() as $parameter) {
                $parameterClass = $parameter->getClass();

                if (!$parameterClass instanceof ReflectionClass) {
                    throw new FactoryError(sprintf(
                        'Parameter %s of Class %s is no Class',
                        $parameter->getName(),
                        $classname
                    ));
                }

                $parameters[] = $this->get($parameterClass->getName());
            }
        }

        return new $classname(...$parameters);
    }

    /**
     * @throws FactoryError
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

    public function setService(string $name, object $class): void
    {
        $this->services[$name] = $class;
    }

    public function setInterface(string $interfaceName, string $className): void
    {
        $this->interfaces[$interfaceName] = $className;
    }

    public function setAbstract(string $abstractName, string $className): void
    {
        $this->abstracts[$abstractName] = $className;
    }
}
