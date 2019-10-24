<?php
namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\Module;
use GibsonOS\Core\Repository\Setting as SettingRepository;

class ModuleSetting extends AbstractSingletonService
{
    /**
     * @var Setting[][]
     */
    private $moduleSettings = [];

    /**
     * @return ModuleSetting|AbstractSingletonService
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @param null|string $key
     * @param null|int $userId
     * @return Setting|Setting[]
     * @throws SelectError
     */
    public function getByRegistry($key = null, $userId = null)
    {
        return $this->getByName($this->getModuleNameByRegistry(), $key, $userId);
    }

    /**
     * @param string $moduleName
     * @param null|string $key
     * @param null|int $userId
     * @return Setting|Setting[]
     * @throws SelectError
     */
    public function getByName($moduleName, $key = null, $userId = null)
    {
        return $this->getById($this->getModuleIdByName($moduleName), $key, $userId);
    }

    /**
     * @param int $moduleId
     * @param null|string $key
     * @param null|int $userId
     * @return Setting|Setting[]
     * @throws SelectError
     */
    public function getById($moduleId, $key = null, $userId = null)
    {
        // Einstellungen nur neu laden wenn sie nicht schon geladen wurden
        if (
            is_null($key) &&
            array_key_exists($moduleId, $this->moduleSettings) &&
            array_key_exists($userId, $this->moduleSettings[$moduleId])
        ) {
            return $this->moduleSettings[$moduleId][$userId];
        }

        $settings = $this->loadSettings($moduleId, $userId, $key);

        if (is_null($key)) {
            $this->moduleSettings[$moduleId][$userId] = $settings;
        }

        return $settings;
    }

    /**
     * @param string $key
     * @param string $value
     * @param null|int $userId
     * @throws SelectError
     * @throws SaveError
     */
    public function setByRegistry($key, $value, $userId = null)
    {
        $this->setByName($this->getModuleNameByRegistry(), $key, $value, $userId);
    }

    /**
     * @param string $moduleName
     * @param string $key
     * @param string $value
     * @param null|int $userId
     * @throws SelectError
     * @throws SaveError
     */
    public function setByName($moduleName, $key, $value, $userId = null)
    {
        $this->setById($this->getModuleIdByName($moduleName), $key, $value, $userId);
    }

    /**
     * @param int $moduleId
     * @param string $key
     * @param string $value
     * @param null|int $userId
     * @throws SaveError
     */
    public function setById($moduleId, $key, $value, $userId = null)
    {
        $settingModel = new Setting();
        $settingModel->setModuleId($moduleId);
        $settingModel->setUserId($userId);
        $settingModel->setKey($key);
        $settingModel->setValue($value);
        $settingModel->save();
    }

    /**
     * @return string
     */
    private function getModuleNameByRegistry()
    {
        $registry = Registry::getInstance();
        return $registry->get('module');
    }

    /**
     * @param string $name
     * @return int
     * @throws SelectError
     */
    private function getModuleIdByName($name)
    {
        $moduleModel = Module::getByName($name);

        return $moduleModel->getId();
    }

    /**
     * Gibt Einstellungen zurück
     *
     * Gibt die Einstellungen zu $moduleId zurück.<br>
     * Wenn $userId null ist wird der aktuelle Benutzer genommen.<br>
     * Wenn $key null ist wird ein array zurück gegeben.
     *
     * @param int $moduleId
     * @param int|null $userId
     * @param string|null $key
     * @return Setting[]|Setting
     * @throws SelectError
     */
    private function loadSettings($moduleId, $userId = null, $key = null)
    {
        $registry = Registry::getInstance();

        // User ID holen
        if (is_null($userId)) {
            if ($registry->exists('session')) {
                $userId = $registry->get('session')->getValueInt('user_id', 0, false);
            } else {
                $userId = 0;
            }
        }

        if (is_null($key)) {
            return SettingRepository::getAll($moduleId, $userId);
        } else {
            return SettingRepository::getByKey($moduleId, $userId, $key);
        }
    }
}