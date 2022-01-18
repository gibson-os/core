<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\ServiceManagerService;
use Psr\Log\LoggerInterface;

abstract class AbstractInstall implements InstallInterface
{
    protected DirService $dirService;

    protected EnvService $envService;

    protected SettingRepository $settingRepository;

    protected ModuleRepository $moduleRepository;

    protected LoggerInterface $logger;

    /**
     * @param ServiceManagerService $serviceManagerService
     *
     * @throws FactoryError
     */
    public function __construct(protected ServiceManagerService $serviceManagerService)
    {
        $this->dirService = $this->serviceManagerService->get(DirService::class);
        $this->envService = $this->serviceManagerService->get(EnvService::class);
        $this->settingRepository = $this->serviceManagerService->get(SettingRepository::class);
        $this->moduleRepository = $this->serviceManagerService->get(ModuleRepository::class);
        $this->logger = $this->serviceManagerService->get(LoggerInterface::class);
    }

    /**
     * @throws GetError
     */
    protected function getFiles(string $path): Generator
    {
        $path = $this->dirService->addEndSlash($path);

        foreach ($this->dirService->getFiles($path) as $file) {
            if (is_dir($file)) {
                yield from $this->getFiles($file);

                continue;
            }

            yield $file;
        }
    }

    protected function getEnvInput(string $key, string $message): Input
    {
        try {
            $value = $this->envService->getString($key);
        } catch (GetError) {
            $value = null;
        }

        return new Input($message, $value);
    }

    protected function getSettingInput(string $moduleName, string $key, string $message): Input
    {
        try {
            $setting = $this->settingRepository->getByKeyAndModuleName($moduleName, null, $key);
            $value = $setting->getValue();
        } catch (SelectError) {
            $value = null;
        }

        return new Input($message, $value);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     */
    protected function setSetting(string $moduleName, string $key, string $value): void
    {
        $module = $this->moduleRepository->getByName($moduleName);

        (new Setting())
            ->setModuleId($module->getId() ?? 0)
            ->setKey($key)
            ->setValue($value)
            ->save()
        ;
    }

    public function getModule(): ?string
    {
        return null;
    }
}
