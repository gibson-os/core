<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\ModuleService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\ActionStore;
use GibsonOS\Core\Store\ModuleStore;
use GibsonOS\Core\Store\SettingStore;
use GibsonOS\Core\Store\TaskStore;
use JsonException;
use ReflectionException;

class ModuleController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function get(
        ModuleStore $moduleStore,
        TaskStore $taskStore,
        ActionStore $actionStore,
        string $node = 'root'
    ): AjaxResponse {
        if ($node === 'root') {
            return $this->returnSuccess($moduleStore->getList());
        }

        if (mb_strpos($node, 't') === 0) {
            $actionStore->setTaskId((int) mb_substr($node, 1));

            return $this->returnSuccess($actionStore->getList());
        }

        $taskStore->setModuleId((int) $node);

        return $this->returnSuccess($taskStore->getList());
    }

    /**
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::WRITE)]
    public function postScan(ModuleService $moduleService, ModuleStore $moduleStore): AjaxResponse
    {
        $moduleService->scan();

        return $this->returnSuccess($moduleStore->getList());
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function getSetting(SettingStore $settingStore, int $moduleId): AjaxResponse
    {
        $settingStore->setModuleId($moduleId);

        return $this->returnSuccess($settingStore->getList(), $settingStore->getCount());
    }
}
