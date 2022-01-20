<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Generator;
use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\InstallDtoInterface;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Install\InstallInterface;
use GibsonOS\Core\Install\SingleInstallInterface;

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
        private FileService $fileService,
        private EnvService $envService,
        #[GetServices(['*/src/Install'], InstallInterface::class)] private array $installers
    ) {
    }

    /**
     * @throws GetError
     * @throws SetError
     * @throws InstallException
     *
     * @return Generator<InstallDtoInterface>|InstallDtoInterface[]
     */
    public function install(string $module = null, string $part = null): iterable
    {
        $modules = $this->getModules();
        $moduleNames = array_map(
            static fn (string $module): string => preg_replace('/.*\/(.*)/', '$1', $module),
            $modules
        );
        $parts = self::PARTS;

        if ($module !== null) {
            if (!in_array($module, $moduleNames)) {
                throw new InstallException(sprintf(
                    'Module "%s" not exists. Existing modules: %s!',
                    $module,
                    implode(', ', $moduleNames)
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

        $configuration = [];
        $installedSingleInstallers = [];

        foreach ($this->installers as $installer) {
            if (!in_array($installer->getPart(), $parts)) {
                continue;
            }

            foreach ($modules as $module) {
                $moduleName = preg_replace('/.*\/(.*)/', '$1', $module);

                if (
                    ($installer->getModule() !== null && $installer->getModule() !== $moduleName) ||
                    in_array($installer::class, $installedSingleInstallers)
                ) {
                    continue;
                }

                foreach ($installer->install($module) as $installDto) {
                    if ($installDto instanceof Configuration) {
                        $configuration = array_merge($configuration, $installDto->getValues());

                        foreach ($installDto->getValues() as $key => $value) {
                            $configuration[$key] = $value;
                            $this->envService->setString($key, $value);
                        }
                    }

                    yield $installDto;
                }

                if ($installer instanceof SingleInstallInterface) {
                    $installedSingleInstallers[] = $installer::class;
                }
            }
        }

        if (!empty($configuration)) {
            $envFilename = realpath(
                dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    '..' . DIRECTORY_SEPARATOR .
                    '..' . DIRECTORY_SEPARATOR .
                    '..' . DIRECTORY_SEPARATOR .
                    '..' . DIRECTORY_SEPARATOR .
                    '..' . DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR . '.env';

            if (!$this->fileService->isWritable($envFilename, true)) {
                throw new InstallException(sprintf('Env file "%s" is not writable!', $envFilename));
            }

            $envFile = file_exists($envFilename) ? file_get_contents($envFilename) : '';
            $oldEnvEntries = [];

            foreach (explode(PHP_EOL, $envFile) as $envEntry) {
                if (empty(trim($envEntry))) {
                    continue;
                }

                if (isset($configuration[explode('=', $envEntry)[0]])) {
                    continue;
                }

                $oldEnvEntries[] = $envEntry . PHP_EOL;
            }

            file_put_contents($envFilename, array_merge($oldEnvEntries, array_map(
                fn (string $key, string $value): string => $key . '=' . $value . PHP_EOL,
                array_keys($configuration),
                array_values($configuration)
            )));

            yield new Success('Configuration file written!');
        }

        yield new Success('Install finished!');
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
