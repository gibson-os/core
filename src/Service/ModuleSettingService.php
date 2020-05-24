<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;

/**
 * @deprecated
 */
class ModuleSettingService extends AbstractService
{
    /**
     * @var Setting[][]|Setting[][][]
     */
    private $moduleSettings = [];

    /**
     * @var RegistryService
     */
    private $registry;

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * ModuleSettingService constructor.
     */
    public function __construct(
        RegistryService $registry,
        ModuleRepository $moduleRepository,
        SettingRepository $settingRepository
    ) {
        $this->registry = $registry;
        $this->moduleRepository = $moduleRepository;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return Setting|Setting[]
     */
    public function getByRegistry(string $key = null, int $userId = null)
    {
        return $this->getByName($this->getModuleNameByRegistry(), $key, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return Setting|Setting[]
     */
    public function getByName(string $moduleName, string $key = null, int $userId = null)
    {
        return $this->getById($this->getModuleIdByName($moduleName), $key, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return Setting|Setting[]
     */
    public function getById(int $moduleId, string $key = null, int $userId = null)
    {
        // Einstellungen nur neu laden wenn sie nicht schon geladen wurden
        if (
            $key === null &&
            array_key_exists($moduleId, $this->moduleSettings) &&
            $userId != null &&
            array_key_exists($userId, $this->moduleSettings[$moduleId])
        ) {
            return $this->moduleSettings[$moduleId][$userId];
        }

        $settings = $this->loadSettings($moduleId, $userId, $key);

        if ($key === null) {
            $this->moduleSettings[$moduleId][$userId ?? 0] = $settings;
        }

        return $settings;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     */
    public function setByRegistry(string $key, string $value, int $userId = null): void
    {
        $this->setByName($this->getModuleNameByRegistry(), $key, $value, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     */
    public function setByName(string $moduleName, string $key, string $value, int $userId = null): void
    {
        $this->setById($this->getModuleIdByName($moduleName), $key, $value, $userId);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     */
    public function setById(int $moduleId, string $key, string $value, int $userId = null): void
    {
        $settingModel = new Setting();
        $settingModel->setModuleId($moduleId);
        $settingModel->setUserId($userId ?? 0);
        $settingModel->setKey($key);
        $settingModel->setValue($value);
        $settingModel->save();
    }

    /**
     * @throws GetError
     */
    private function getModuleNameByRegistry(): string
    {
        return (string) $this->registry->get('module');
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    private function getModuleIdByName(string $name): int
    {
        $moduleModel = $this->moduleRepository->getByName($name);

        return $moduleModel->getId() ?? 0;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return Setting[]|Setting
     */
    private function loadSettings(int $moduleId, int $userId = null, string $key = null)
    {
        // User ID holen
        if ($userId === null) {
            if ($this->registry->exists('session')) {
                $userId = $this->registry->get('session')->getValueInt('user_id', 0, false);
            } else {
                $userId = 0;
            }
        }

        if ($key === null) {
            return $this->settingRepository->getAll($moduleId, $userId);
        }

        return $this->settingRepository->getByKey($moduleId, $userId, $key);
    }
}
