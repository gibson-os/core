<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\RegistryService;

/**
 * @deprecated
 */
class RegistryFactory
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
