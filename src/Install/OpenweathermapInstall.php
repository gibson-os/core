<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;

class OpenweathermapInstall extends AbstractInstall implements PriorityInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $apiKeyInput = $this->getEnvInput('OPENWEATHERMAP_API_KEY', 'What is the openweathermap API key?');
        yield $apiUrlInput = $this->getEnvInput('OPENWEATHERMAP_URL', 'What is the openweathermap API URL?');

        yield (new Configuration('Openweathermap settings saved!'))
            ->setValue('OPENWEATHERMAP_API_KEY', $apiKeyInput->getValue() ?? '')
            ->setValue('OPENWEATHERMAP_URL', $apiUrlInput->getValue() ?? '')
        ;
    }

    #[Override]
    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    #[Override]
    public function getModule(): ?string
    {
        return 'core';
    }

    #[Override]
    public function getPriority(): int
    {
        return 800;
    }
}
