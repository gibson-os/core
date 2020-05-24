<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;

class DesktopController extends AbstractController
{
    private const DESKTOP_KEY = 'desktop';

    private const APPS_KEY = 'apps';

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function index(SettingRepository $settingRepository): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $moduleName = $this->requestService->getModuleName();
        $desktop = $settingRepository->getByKeyAndModuleName(
            $moduleName,
            $this->sessionService->getUserId() ?? 0,
            self::DESKTOP_KEY
        );
        $apps = $settingRepository->getByKeyAndModuleName(
            $moduleName,
            $this->sessionService->getUserId() ?? 0,
            self::APPS_KEY
        );

        return $this->returnSuccess([
            self::DESKTOP_KEY => JsonUtility::decode($desktop->getValue()),
            self::APPS_KEY => JsonUtility::decode($apps->getValue()),
        ]);
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws SaveError
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function save(ModuleRepository $moduleRepository, array $items): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);

        $module = $moduleRepository->getByName($this->requestService->getModuleName());

        (new Setting())
            ->setUserId($this->sessionService->getUserId() ?? 0)
            ->setModule($module)
            ->setKey(self::DESKTOP_KEY)
            ->setValue(JsonUtility::encode($items))
            ->save()
        ;

        return $this->returnSuccess();
    }
}
