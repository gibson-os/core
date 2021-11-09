<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\Response\AjaxResponse;
use JsonException;

class IndexController extends AbstractController
{
    /**
     * @throws JsonException
     */
    #[GetSetting(DesktopController::DESKTOP_KEY)]
    #[GetSetting(DesktopController::APPS_KEY)]
    #[GetSetting(DesktopController::TOOLS_KEY)]
    public function index(
        DesktopController $desktopController,
        ?Setting $desktop,
        ?Setting $apps,
        ?Setting $tools
    ): AjaxResponse {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->index($desktop, $apps, $tools);
    }
}
