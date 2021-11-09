<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\Setting as SettingAttribute;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use JsonException;

class IndexController extends AbstractController
{
    /**
     * @throws JsonException
     */
    #[CheckPermission(Permission::READ)]
    #[SettingAttribute(DesktopController::DESKTOP_KEY)]
    #[SettingAttribute(DesktopController::APPS_KEY)]
    #[SettingAttribute(DesktopController::TOOLS_KEY)]
    public function index(
        DesktopController $desktopController,
        ?string $desktop,
        ?string $apps,
        ?string $tools
    ): AjaxResponse {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->index($desktop, $apps, $tools);
    }
}
