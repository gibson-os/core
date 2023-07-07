<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class NewRelicInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $licenseInput = $this->getEnvInput('NEW_RELIC_LICENSE', 'If you have New Relic enter your license');

        if (trim($licenseInput->getValue() ?? '') === '') {
            return;
        }

        yield (new Configuration('New Relic settings saved!'))
            ->setValue('NEW_RELIC_LICENSE', $licenseInput->getValue() ?? '')
        ;
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
