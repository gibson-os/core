<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class C2dmInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): \Generator
    {
        yield $emailInput = $this->getEnvInput('C2DM_EMAIL', 'What is the c2dm email address?');
        yield $passwordInput = $this->getEnvInput('C2DM_PASSWORD', 'What is the c2dm password?');
        yield $tokenInput = $this->getEnvInput('C2DM_AUTH', 'What is the c2dm access token?');

        yield (new Configuration('C2dm settings saved!'))
            ->setValue('C2DM_EMAIL', $emailInput->getValue() ?? '')
            ->setValue('C2DM_PASSWORD', $passwordInput->getValue() ?? '')
            ->setValue('C2DM_AUTH', $tokenInput->getValue() ?? '')
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
