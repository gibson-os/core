<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;

class DesktopController extends AbstractController
{
    public const DESKTOP_KEY = 'desktop';

    public const APPS_KEY = 'apps';

    public const TOOLS_KEY = 'tools';

    /**
     * @throws \JsonException
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
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::WRITE)]
    public function save(ModelManager $modelManager, ModuleRepository $moduleRepository, array $items): AjaxResponse
    {
        $module = $moduleRepository->getByName($this->requestService->getModuleName());

        foreach ($items as &$item) {
            if (empty($item['params'])) {
                $item['params'] = null;
            }
        }

        $modelManager->save(
            (new Setting())
                ->setUserId($this->sessionService->getUserId() ?? 0)
                ->setModule($module)
                ->setKey(self::DESKTOP_KEY)
                ->setValue(JsonUtility::encode($items))
        );

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     * @throws \JsonException
     * @throws SelectError
     */
    #[CheckPermission(Permission::WRITE)]
    public function add(
        ModelManager $modelManager,
        ModuleRepository $moduleRepository,
        #[GetSetting(self::DESKTOP_KEY)] ?Setting $desktop,
        array $items,
    ): AjaxResponse {
        /** @var array $desktopItems */
        $desktopItems = JsonUtility::decode($desktop?->getValue() ?? '[]');
        array_push($desktopItems, ...$items);

        if ($desktop === null) {
            $module = $moduleRepository->getByName($this->requestService->getModuleName());
            $desktop = (new Setting())
                ->setUserId($this->sessionService->getUserId() ?? 0)
                ->setModule($module)
                ->setKey(self::DESKTOP_KEY)
            ;
        }

        $modelManager->saveWithoutChildren($desktop->setValue(JsonUtility::encode($desktopItems)));

        return $this->returnSuccess();
    }
}
