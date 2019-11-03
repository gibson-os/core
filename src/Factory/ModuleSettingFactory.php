<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ModuleSettingService;

class ModuleSettingFactory
{
    /**
     * @return ModuleSettingService
     */
    public static function create(): ModuleSettingService
    {
        /** @var ModuleSettingService $moduleSetting */
        $moduleSetting = ModuleSettingService::getInstance();

        return $moduleSetting;
    }
}
