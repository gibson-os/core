<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ModuleSetting as ModuleSettingService;

class ModuleSetting
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
