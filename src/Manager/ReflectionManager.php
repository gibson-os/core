<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use ReflectionClass;
use ReflectionException;

class ReflectionManager
{
    /**
     * @var ReflectionClass[]
     */
    private array $classes = [];

    /**
     * @param class-string|object $className
     *
     * @throws ReflectionException
     */
    public function getReflectionClass(string|object $className): ReflectionClass
    {
        if (!isset($this->classes[$className])) {
            $this->classes[$className] = new ReflectionClass($className);
        }

        return $this->classes[$className];
    }
}
