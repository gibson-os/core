<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use JsonException;

class IndexController extends AbstractController
{
    /**
     * @throws JsonException
     */
    #[CheckPermission(Permission::READ)]
    public function index(DesktopController $desktopController, SettingRepository $settingRepository): AjaxResponse
    {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->index($settingRepository);
    }
}
