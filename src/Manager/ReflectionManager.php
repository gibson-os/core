<?php
declare(strict_types=1);

namespace GibsonOS\Core\Manager;

use ReflectionClass;
use ReflectionException;

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
}
