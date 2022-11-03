<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class ApacheInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): \Generator
    {
        yield $apacheUserInput = $this->getEnvInput('APACHE_USER', 'What is the apache username?');
        yield $apacheGroupInput = $this->getEnvInput('APACHE_GROUP', 'What is the apache group?');
        yield $webUrlInput = $this->getEnvInput('WEB_URL', 'What is the URL? Start with http:// or https://');
        yield (new Configuration('Apache configuration generated!'))
            ->setValue('APACHE_USER', $apacheUserInput->getValue() ?? '')
            ->setValue('APACHE_GROUP', $apacheGroupInput->getValue() ?? '')
            ->setValue('WEB_URL', $webUrlInput->getValue() ?? '')
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
