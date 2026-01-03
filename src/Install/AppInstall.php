<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;

class AppInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $appNameInput = $this->getEnvInput('APP_NAME', 'Please enter a name for your app');

        yield (new Configuration('App settings saved!'))
            ->setValue('APP_NAME', $appNameInput->getValue() ?? '')
        ;
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    #[Override]
    public function getPriority(): int
    {
        return 800;
    }
}
