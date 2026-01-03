<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use Override;

class MiddlewareInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    #[Override]
    public function install(string $module): Generator
    {
        yield $middlewareUrlInput = $this->getEnvInput(
            'MIDDLEWARE_URL',
            'What is the middleware URL?',
        );

        $middlewareUrl = $middlewareUrlInput->getValue() ?? '';

        if (mb_substr($middlewareUrl, -1) !== '/') {
            $middlewareUrl .= '/';
        }

        yield (new Configuration('Middleware configuration generated!'))
            ->setValue('MIDDLEWARE_URL', $middlewareUrl)
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
