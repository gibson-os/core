<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class MiddlewareInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): \Generator
    {
        yield $middlewareUrlInput = $this->getEnvInput(
            'MIDDLEWARE_URL',
            'What is the middleware URL?'
        );

        yield (new Configuration('Middleware configuration generated!'))
            ->setValue('MIDDLEWARE_URL', $middlewareUrlInput->getValue() ?? '')
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
