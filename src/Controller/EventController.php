<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Store\EventStore;

class EventController extends AbstractController
{
    /**
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function index(
        EventStore $eventStore,
        ?int $masterId,
        ?int $slaveId,
        int $start = 0,
        int $limit = 0,
        array $sort = []
    ): ResponseInterface {
        $this->checkPermission(PermissionService::READ);

        $eventStore
            ->setMasterId($masterId)
            ->setSlaveId($slaveId)
        ;
        $eventStore->setLimit($limit, $start);
        $eventStore->setSortByExt($sort);

        return $this->returnSuccess($eventStore->getList(), $eventStore->getCount());
    }
}
