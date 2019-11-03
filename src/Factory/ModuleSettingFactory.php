<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ModuleSetting;

class ModuleSettingFactory
{
    /**
     * @return ModuleSetting
     */
    public static function create(): ModuleSetting
    {
        /** @var ModuleSetting $moduleSetting */
        $moduleSetting = ModuleSetting::getInstance();

        return $moduleSetting;
    }
}
