<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class OpenTelemetryInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    /**
     * @throws GetError
     */
    public function install(string $module): Generator
    {
        yield $endpointInput = $this->getEnvInput('OTEL_EXPORTER_OTLP_ENDPOINT', 'If you use OpenTelemetry enter the ip or hostname');

        if (trim($endpointInput->getValue() ?? '') === '') {
            return;
        }

        yield (new Configuration('Open Telemetry settings saved!'))
            ->setValue('OTEL_EXPORTER_OTLP_ENDPOINT', $endpointInput->getValue() ?? '')
            ->setValue('OTEL_SERVICE_NAME', $this->envService->getString('APP_NAME'))
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
