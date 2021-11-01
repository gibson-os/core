<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use JsonException;

class IndexController extends AbstractController
{
    /**
     * @throws JsonException
     */
    public function index(DesktopController $desktopController, SettingRepository $settingRepository): AjaxResponse
    {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->index($settingRepository);
    }
}
