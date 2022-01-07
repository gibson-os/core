<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;

class DesktopController extends AbstractController
{
    public const DESKTOP_KEY = 'desktop';

    public const APPS_KEY = 'apps';

    public const TOOLS_KEY = 'tools';

    /**
     * @throws JsonException
     */
    #[CheckPermission(Permission::READ)]
    public function index(
        #[GetSetting(self::DESKTOP_KEY)] ?Setting $desktop,
        #[GetSetting(self::APPS_KEY)] ?Setting $apps,
        #[GetSetting(self::TOOLS_KEY)] ?Setting $tools
    ): AjaxResponse {
        return $this->returnSuccess([
            self::DESKTOP_KEY => JsonUtility::decode($desktop?->getValue() ?: '[]'),
            self::APPS_KEY => JsonUtility::decode($apps?->getValue() ?: '[]'),
            self::TOOLS_KEY => JsonUtility::decode($tools?->getValue() ?: '[]'),
        ]);
    }

    /**
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     */
    #[CheckPermission(Permission::WRITE)]
    public function save(ModuleRepository $moduleRepository, array $items): AjaxResponse
    {
        $module = $moduleRepository->getByName($this->requestService->getModuleName());

        foreach ($items as &$item) {
            if (empty($item['params'])) {
                $item['params'] = null;
            }
        }

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
