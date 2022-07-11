<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Input;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionException;

abstract class AbstractInstall implements InstallInterface
{
    protected DirService $dirService;

    protected EnvService $envService;

    protected SettingRepository $settingRepository;

    protected ModuleRepository $moduleRepository;

    protected ModelManager $modelManager;

    protected LoggerInterface $logger;

    /**
     * @throws FactoryError
     */
    public function __construct(protected ServiceManager $serviceManagerService)
    {
        $this->dirService = $this->serviceManagerService->get(DirService::class);
        $this->envService = $this->serviceManagerService->get(EnvService::class);
        $this->settingRepository = $this->serviceManagerService->get(SettingRepository::class);
        $this->moduleRepository = $this->serviceManagerService->get(ModuleRepository::class);
        $this->modelManager = $this->serviceManagerService->get(ModelManager::class);
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
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     */
    protected function setSetting(string $moduleName, string $key, string $value): void
    {
        $module = $this->moduleRepository->getByName($moduleName);

        try {
            $setting = $this->settingRepository->getByKeyAndModuleName($moduleName, null, $key);
        } catch (SelectError) {
            $setting = new Setting();
        }

        $this->modelManager->save(
            $setting
                ->setModuleId($module->getId() ?? 0)
                ->setKey($key)
                ->setValue($value)
        );
    }

    /**
     * @throws InstallException
     */
    protected function checkSizeInput(Input $input): string
    {
        $value = $input->getValue() ?? '';

        if ($value === '0') {
            return $value;
        }

        preg_match('/(\d+)(\w*)/', $value, $hits);

        if (
            count($hits) < 2 ||
            !is_numeric($hits[1])
        ) {
            throw new InstallException(sprintf('Value "%s" is no number!', $hits[1]));
        }

        $types = ['k', 'kb', 'm', 'mb', 'g', 'gb'];

        if (
            array_key_exists(2, $hits) &&
            !in_array($hits[2], $types)
        ) {
            return throw new InstallException(sprintf(
                '"%s" is no valid value! Possible: %s',
                $hits[2],
                implode(', ', $types)
            ));
        }

        return $value;
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function addApp(string $text, string $module, string $task, string $action, string $icon): self
    {
        try {
            $appsSetting = $this->settingRepository->getByKeyAndModuleName('core', null, 'apps');
            $apps = JsonUtility::decode($appsSetting->getValue());
        } catch (SelectError) {
            $coreModule = $this->moduleRepository->getByName('core');
            $appsSetting = (new Setting())
                ->setModule($coreModule)
                ->setKey('apps')
            ;
            $apps = [];
        }

        foreach ($apps as $existingApp) {
            if (
                $module === $existingApp['module'] &&
                $task === $existingApp['task'] &&
                $action === $existingApp['action']
            ) {
                return $this;
            }
        }

        $apps[] = [
            'text' => $text,
            'module' => $module,
            'task' => $task,
            'action' => $action,
            'icon' => $icon,
        ];
        $this->modelManager->save($appsSetting->setValue(JsonUtility::encode($apps)));

        return $this;
    }

    public function getModule(): ?string
    {
        return null;
    }
}
