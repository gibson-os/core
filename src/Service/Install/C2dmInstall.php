<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class C2dmInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    /**
     * @throws SaveError
     */
    public function install(string $module): Generator
    {
        yield $emailInput = $this->getSettingInput(
            'core',
            'c2dm_email',
            'What is the c2dm email address?'
        );
        yield $passwordInput = $this->getSettingInput(
            'core',
            'c2dm_password',
            'What is the c2dm password?'
        );
        yield $tokenInput = $this->getSettingInput(
            'core',
            'c2dm_auth',
            'What is the c2dm access token?'
        );

        $this->setSetting('core', 'c2dm_email', $emailInput->getValue() ?? '');
        $this->setSetting('core', 'c2dm_password', $passwordInput->getValue() ?? '');
        $this->setSetting('core', 'c2dm_auth', $tokenInput->getValue() ?? '');
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 500;
    }
}
