<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\Desktop\ItemRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;

class IndexController extends AbstractController
{
    /**
     * @throws JsonException
     */
    public function get(
        DesktopController $desktopController,
        ItemRepository $itemRepository,
        ModelWrapper $modelWrapper,
        #[GetSetting(DesktopController::APPS_KEY)]
        ?Setting $apps,
        #[GetSetting(DesktopController::TOOLS_KEY)]
        ?Setting $tools,
    ): AjaxResponse {
        if (!$this->sessionService->isLogin()) {
            return $this->returnSuccess();
        }

        return $desktopController->get(
            $itemRepository,
            $apps,
            $tools,
            $this->sessionService->getUser() ?? new User($modelWrapper),
        );
    }
}
