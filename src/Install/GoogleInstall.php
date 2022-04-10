<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class GoogleInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $googleApplicationCredentialsInput = $this->getEnvInput(
            'GOOGLE_APPLICATION_CREDENTIALS',
            'What is the path of the google application credentials?'
        );

        yield (new Configuration('Google application configuration generated!'))
            ->setValue('GOOGLE_APPLICATION_CREDENTIALS', $googleApplicationCredentialsInput->getValue() ?? '')
        ;

        yield $this->getSettingInput(
            'core',
            'fcmProjectId',
            'What is the FCM project id?'
        );
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
