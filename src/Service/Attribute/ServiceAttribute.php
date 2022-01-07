<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use ReflectionParameter;

class ServiceAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws FactoryError
     * @throws GetError
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter
    ): array {
        if (!$attribute instanceof GetServices) {
            return [];
        }

        $vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'gibson-os'
        ) . DIRECTORY_SEPARATOR;
        $classes = [];

        foreach ($attribute->getDirs() as $dir) {
            $classes = array_merge(
                $classes,
                $this->serviceManagerService->getAll($vendorPath . lcfirst($dir), $attribute->getInstanceOf())
            );
        }

        usort(
            $classes,
            fn (object $a, object $b) => ($a instanceof PriorityInterface ? $a->getPriority() : 0) <=> ($b instanceof PriorityInterface ? $b->getPriority() : 0)
        );

        return $classes;
    }
}
