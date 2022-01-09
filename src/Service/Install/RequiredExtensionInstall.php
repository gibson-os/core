<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;

class RequiredExtensionInstall implements InstallInterface, PriorityInterface
{
    /**
     * @param RequiredExtensionInterface[] $services
     */
    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    public function install(string $module): Generator
    {
        $services = $this->serviceManagerService->getAll(
            ucfirst($module) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Install',
            RequiredExtensionInterface::class
        );

        foreach ($services as $service) {
            $service->checkRequiredExtensions();
        }

        yield new Success(sprintf('Required extensions for module "%s" checked!', $module));
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 1000;
    }
}
