<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

abstract class AbstractSingletonFactory implements FactoryInterface
{
    /**
     * @var object[]
     */
    private static $instances = [];

    abstract protected static function createInstance();

    public static function create()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = static::createInstance();
        }

        return self::$instances[$class];
    }
}
