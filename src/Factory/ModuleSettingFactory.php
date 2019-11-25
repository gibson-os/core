<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ModuleSettingService;

class ModuleSettingFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): ModuleSettingService
    {
        return new ModuleSettingService(RegistryFactory::create());
    }

    public static function create(): ModuleSettingService
    {
        /** @var ModuleSettingService $service */
        $service = parent::create();

        return $service;
    }
}
