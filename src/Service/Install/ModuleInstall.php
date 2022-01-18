<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\ModuleService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;

class ModuleInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function __construct(
        ServiceManagerService $serviceManagerService,
        private ModuleService $moduleService
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws GetError
     * @throws SaveError
     */
    public function install(string $module): Generator
    {
        $this->moduleService->scan();

        yield new Success('Modules scanned!');
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 600;
    }
}
