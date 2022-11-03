<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\Response\AjaxResponse;

class IndexController extends AbstractController
{
    /**
     * @throws \JsonException
     */
    public function index(
        DesktopController $desktopController,
        #[GetSetting(DesktopController::DESKTOP_KEY)] ?Setting $desktop,
        #[GetSetting(DesktopController::APPS_KEY)] ?Setting $apps,
        #[GetSetting(DesktopController::TOOLS_KEY)] ?Setting $tools
    ): AjaxResponse {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->index($desktop, $apps, $tools);
    }
}
