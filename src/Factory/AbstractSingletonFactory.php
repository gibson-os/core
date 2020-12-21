<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

abstract class AbstractSingletonFactory implements FactoryInterface
{
    /**
     * @var object[]
     */
    private static array $instances = [];

    abstract protected static function createInstance();

    public static function create(): object
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = static::createInstance();
        }

        return self::$instances[$class];
    }
}
