<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Service\Install\InstallInterface;
use Psr\Log\LoggerInterface;

class InstallService
{
    public const PART_DATABASE = 'db';

    public const PART_CONFIG = 'config';

    public const PART_DATA = 'data';

    public const PART_CRONJOB = 'cronjob';

    private const PARTS = [
        self::PART_DATABASE,
        self::PART_CONFIG,
        self::PART_DATA,
        self::PART_CRONJOB,
    ];

    /**
     * @param InstallInterface[] $installers
     */
    public function __construct(
        private DirService $dirService,
        private LoggerInterface $logger,
        #[GetServices(['Core/src/Service/Install'], InstallInterface::class)] private array $installers
    ) {
    }

    /**
     * @throws InstallException
     * @throws GetError
     */
    public function install(string $module = null, string $part = null): void
    {
        $modules = $this->getModules();
        $parts = self::PARTS;

        if ($module !== null) {
            if (!in_array($module, $modules)) {
                throw new InstallException(sprintf(
                    'Module "%s" not exists. Existing modules: %s!',
                    $module,
                    implode(', ', $modules)
                ));
            }

            $modules = [$module];
        }

        if ($part !== null) {
            if (!in_array($part, $parts)) {
                throw new InstallException(sprintf(
                    'Part "%s" not exists. Existing parts: %s!',
                    $part,
                    implode(', ', self::PARTS)
                ));
            }

            $parts = [$part];
        }

        foreach ($modules as $module) {
            $this->installModule($module, $parts);
        }
    }

    private function installModule(string $module, array $parts): void
    {
        foreach ($this->installers as $installer) {
            if (!in_array($installer->getPart(), $parts)) {
                continue;
            }

            $installer->install($module);
        }
    }

    /**
     * @throws GetError
     */
    private function getModules(): array
    {
        $vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
        $modules = [];

        foreach ($this->dirService->getFiles($vendorPath) as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $modules[] = $dir;
        }

        return $modules;
    }
}
