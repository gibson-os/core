<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Cronjob\TimeStore;
use GibsonOS\Core\Store\CronjobStore;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class CronjobController extends AbstractController
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    #[CheckPermission([Permission::READ])]
    public function get(CronjobStore $cronjobStore, int $limit = 100, int $start = 0, array $sort = []): AjaxResponse
    {
        $cronjobStore->setLimit($limit, $start);
        $cronjobStore->setSortByExt($sort);

        return $this->returnSuccess($cronjobStore->getList(), $cronjobStore->getCount());
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function getTimes(
        TimeStore $timeStore,
        #[GetModel]
        Cronjob $cronjob,
    ): AjaxResponse {
        $timeStore->setCronjob($cronjob);

        return $this->returnSuccess($timeStore->getList());
    }
}
