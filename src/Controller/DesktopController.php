<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\Setting as SettingAttribute;
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
    #[SettingAttribute(self::DESKTOP_KEY)]
    #[SettingAttribute(self::APPS_KEY)]
    #[SettingAttribute(self::TOOLS_KEY)]
    public function index(?string $desktop, ?string $apps, ?string $tools): AjaxResponse
    {
        return $this->returnSuccess([
            self::DESKTOP_KEY => JsonUtility::decode($desktop ?: '[]'),
            self::APPS_KEY => JsonUtility::decode($apps ?: '[]'),
            self::TOOLS_KEY => JsonUtility::decode($tools ?: '[]'),
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
