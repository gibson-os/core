<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Service\ModuleService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\ActionStore;
use GibsonOS\Core\Store\ModuleStore;
use GibsonOS\Core\Store\SettingStore;
use GibsonOS\Core\Store\TaskStore;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;

class ModuleController extends AbstractController
{
    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function get(
        ModuleStore $moduleStore,
        TaskStore $taskStore,
        ActionStore $actionStore,
        string $node = 'root',
    ): AjaxResponse {
        if ($node === 'root') {
            return $moduleStore->getAjaxResponse();
        }

        if (mb_strpos($node, 't') === 0) {
            $actionStore->setTaskId((int) mb_substr($node, 1));

            return $actionStore->getAjaxResponse();
        }

        $taskStore->setModuleId((int) $node);

        return $taskStore->getAjaxResponse();
    }

    /**
     * @throws ClientException
     * @throws GetError
     * @throws JsonException
     * @throws RecordException
     * @throws SaveError
     */
    #[CheckPermission([Permission::MANAGE, Permission::WRITE])]
    public function postScan(ModuleService $moduleService, ModuleStore $moduleStore): AjaxResponse
    {
        $moduleService->scan();

        return $moduleStore->getAjaxResponse();
    }

    #[CheckPermission([Permission::MANAGE, Permission::READ])]
    public function getSetting(
        #[GetStore]
        SettingStore $settingStore,
        int $moduleId,
    ): AjaxResponse {
        $settingStore->setModuleId($moduleId);

        return $settingStore->getAjaxResponse();
    }
}
