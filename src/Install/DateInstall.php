<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class DateInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $timezoneInput = $this->getEnvInput('TIMEZONE', 'What is the timezone?');
        yield $dateLatitudeInput = $this->getEnvInput('DATE_LATITUDE', 'What is the timezone latitude?');
        yield $dateLongitudeInput = $this->getEnvInput('DATE_LONGITUDE', 'What is the timezone longitude?');

        yield (new Configuration('Date settings saved!'))
            ->setValue('TIMEZONE', $timezoneInput->getValue() ?? '')
            ->setValue('DATE_LATITUDE', $dateLatitudeInput->getValue() ?? '')
            ->setValue('DATE_LONGITUDE', $dateLongitudeInput->getValue() ?? '')
        ;
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 800;
    }
}
