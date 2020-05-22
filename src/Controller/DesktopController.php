<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;

class DesktopController extends AbstractController
{
    private const DESKTOP_KEY = 'desktop';

    public function save(ModuleRepository $moduleRepository, array $items): AjaxResponse
    {
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
