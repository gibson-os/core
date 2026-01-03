<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetClassNames;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;
use ReflectionParameter;

class ServiceAttribute implements ParameterAttributeInterface, AttributeServiceInterface
{
    public function __construct(
        private readonly ServiceManager $serviceManagerService,
        private readonly DirService $dirService,
    ) {
    }

    /**
     * @throws FactoryError
     * @throws GetError
     */
    #[Override]
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter,
    ): array {
        if (
            !$attribute instanceof GetServices
            && !$attribute instanceof GetClassNames
        ) {
            return [];
        }

        $vendorPath = (realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'gibson-os',
        ) ?: '') . DIRECTORY_SEPARATOR;
        $classes = [];
        $dirs = [];
        $modules = [];

        foreach ($this->dirService->getFiles($vendorPath) as $modulePath) {
            if (!is_dir($modulePath)) {
                continue;
            }

            $modules[] = str_replace($vendorPath, '', $modulePath);
        }

        foreach ($attribute->getDirs() as $dir) {
            if (mb_strpos($dir, '*') === 0) {
                foreach ($modules as $module) {
                    $dirs[] = $module . mb_substr($dir, 1);
                }

                continue;
            }

            $dirs[] = $dir;
        }

        foreach ($dirs as $dir) {
            $classes = array_merge(
                $classes,
                $attribute instanceof GetServices
                    ? $this->serviceManagerService->getAll($vendorPath . lcfirst($dir), $attribute->getInstanceOf())
                    : $this->serviceManagerService->getClassNames($vendorPath . lcfirst($dir)),
            );
        }

        if ($attribute instanceof GetServices) {
            usort(
                $classes,
                fn ($a, $b) => ($b instanceof PriorityInterface ? $b->getPriority() : 0) <=> ($a instanceof PriorityInterface ? $a->getPriority() : 0),
            );
        }

        return $classes;
    }
}
