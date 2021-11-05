<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\ActionStore;
use GibsonOS\Core\Store\ModuleStore;
use GibsonOS\Core\Store\TaskStore;

class ModuleController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(
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

    #[CheckPermission(Permission::MANAGE + Permission::READ)]
    public function scan(ModuleStore $moduleStore): AjaxResponse
    {
        return $this->returnSuccess($moduleStore->getList());
    }
}
