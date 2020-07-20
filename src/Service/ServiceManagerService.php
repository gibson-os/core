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
    private $services = [];

    public function __construct()
    {
        $this->services[self::class] = $this;
    }

    /**
     * @throws FactoryError
     */
    public function get(string $classname): object
    {
        if (mb_strpos($classname, '\\') === 0) {
            $classname = substr($classname, 1);
        }

        if (!class_exists($classname)) {
            throw new FactoryError(sprintf('Class %s does not exists', $classname));
        }

        $class = $this->getByFactory($classname);

        if (!empty($class) && get_class($class) === $classname) {
            return $class;
        }

        if (isset($this->services[$classname])) {
            return $this->services[$classname];
        }

        $class = $this->getByCreate($classname);

        if (get_class($class) === $classname) {
            $this->services[$classname] = $class;

            return $class;
        }

        throw new FactoryError(sprintf('Class %s could not be created', $classname));
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
}
