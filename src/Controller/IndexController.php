<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;

class IndexController extends AbstractController
{
    private const DESKTOP_KEY = 'desktop';

    private const APPS_KEY = 'apps';

    public function index(ModuleRepository $moduleRepository, SettingRepository $settingRepository): AjaxResponse
    {
        if (!$this->sessionService->isLogin()) {
            return $this->returnFailure('Login required!');
        }

        $module = $moduleRepository->getByName($this->requestService->getModuleName());
        $desktop = $settingRepository->getByKey(
            $module->getId() ?? 0,
            $this->sessionService->getUserId() ?? 0,
            self::DESKTOP_KEY
        );
        $apps = $settingRepository->getByKey(
            $module->getId() ?? 0,
            $this->sessionService->getUserId() ?? 0,
            self::APPS_KEY
        );

        return $this->returnSuccess([
            self::DESKTOP_KEY => JsonUtility::decode($desktop->getValue()),
            self::APPS_KEY => JsonUtility::decode($apps->getValue()),
        ]);
    }
}
