<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

abstract class AbstractSingletonService extends AbstractService
{
    /**
     * @var AbstractSingletonService[]
     */
    private static $instances = [];

    /**
     * Konstruktor.
     *
     * Keine Instanzen erlauben.
     */
    private function __construct()
    {
    }

    /**
     * Klonen.
     *
     * Keine Klonen erlauben.
     */
    private function __clone()
    {
    }

    /**
     * @return AbstractSingletonService
     */
    public static function getInstance(): AbstractSingletonService
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }
}
