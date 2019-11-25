<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\RegistryService;

/**
 * @deprecated
 */
class RegistryFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): RegistryService
    {
        return new RegistryService();
    }

    public static function create(): RegistryService
    {
        /** @var RegistryService $service */
        $service = parent::create();

        return $service;
    }
}
