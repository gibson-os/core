<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\ModuleSettingService;

/**
 * @deprecated
 */
class ModuleSettingFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): ModuleSettingService
    {
        return new ModuleSettingService(RegistryFactory::create(), new ModuleRepository(), new SettingRepository());
    }

    public static function create(): ModuleSettingService
    {
        /** @var ModuleSettingService $service */
        $service = parent::create();

        return $service;
    }
}
