<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Cronjob\TimeStore;
use GibsonOS\Core\Store\CronjobStore;

class CronjobController extends AbstractController
{
    /**
     * @throws GetError
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(CronjobStore $cronjobStore, int $limit = 100, int $start = 0, array $sort = []): AjaxResponse
    {
        $cronjobStore->setLimit($limit, $start);
        $cronjobStore->setSortByExt($sort);

        return $this->returnSuccess($cronjobStore->getList(), $cronjobStore->getCount());
    }

    #[CheckPermission(Permission::READ)]
    public function times(TimeStore $timeStore): AjaxResponse
    {
        return $this->returnSuccess($timeStore->getList());
    }
}
