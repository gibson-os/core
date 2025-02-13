<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Cronjob\TimeStore;
use GibsonOS\Core\Store\CronjobStore;

class CronjobController extends AbstractController
{
    #[CheckPermission([Permission::READ])]
    public function get(
        #[GetStore]
        CronjobStore $cronjobStore,
    ): AjaxResponse {
        return $cronjobStore->getAjaxResponse();
    }

    #[CheckPermission([Permission::READ])]
    public function getTimes(
        #[GetStore]
        TimeStore $timeStore,
        #[GetModel]
        Cronjob $cronjob,
    ): AjaxResponse {
        $timeStore->setCronjob($cronjob);

        return $timeStore->getAjaxResponse();
    }
}
