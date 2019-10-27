<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Registry as RegistryService;

/**
 * @deprecated
 */
class Registry
{
    /**
     * @return RegistryService
     */
    public static function create(): RegistryService
    {
        /** @var RegistryService $registry */
        $registry = RegistryService::getInstance();

        return $registry;
    }
}
