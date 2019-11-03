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

class ModuleSetting extends AbstractSingletonService
{
    /**
     * @var Setting[][]
     */
    private $moduleSettings = [];

    /**
     * @param string|null $key
     * @param int|null    $userId
     *
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
     * @param string      $moduleName
     * @param string|null $key
     * @param int|null    $userId
     *
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
     * @param int         $moduleId
     * @param string|null $key
     * @param int|null    $userId
     *
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

        if (null === $key) {
            $this->moduleSettings[$moduleId][$userId] = $settings;
        }

        return $settings;
    }

    /**
     * @param string   $key
     * @param string   $value
     * @param int|null $userId
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     */
    public function setByRegistry(string $key, string $value, int $userId = null)
    {
        $this->setByName($this->getModuleNameByRegistry(), $key, $value, $userId);
    }

    /**
     * @param string   $moduleName
     * @param string   $key
     * @param string   $value
     * @param int|null $userId
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     */
    public function setByName(string $moduleName, string $key, string $value, int $userId = null)
    {
        $this->setById($this->getModuleIdByName($moduleName), $key, $value, $userId);
    }

    /**
     * @param int      $moduleId
     * @param string   $key
     * @param string   $value
     * @param int|null $userId
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     */
    public function setById(int $moduleId, string $key, string $value, int $userId = null)
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
     *
     * @return string
     */
    private function getModuleNameByRegistry(): string
    {
        /** @var Registry $registry */
        $registry = Registry::getInstance();

        return (string) $registry->get('module');
    }

    /**
     * @param string $name
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return int
     */
    private function getModuleIdByName(string $name): int
    {
        $moduleModel = ModuleRepository::getByName($name);

        return $moduleModel->getId();
    }

    /**
     * @param int         $moduleId
     * @param int|null    $userId
     * @param string|null $key
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return Setting[]|Setting
     */
    private function loadSettings(int $moduleId, int $userId = null, string $key = null)
    {
        /** @var Registry $registry */
        $registry = Registry::getInstance();

        // User ID holen
        if (null === $userId) {
            if ($registry->exists('session')) {
                $userId = $registry->get('session')->getValueInt('user_id', 0, false);
            } else {
                $userId = 0;
            }
        }

        if (null === $key) {
            return SettingRepository::getAll($moduleId, $userId);
        }

        return SettingRepository::getByKey($moduleId, $userId, $key);
    }
}
