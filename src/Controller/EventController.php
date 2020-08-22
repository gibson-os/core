<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\MethodStore;
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
        int $start = 0,
        int $limit = 0,
        array $sort = []
    ): ResponseInterface {
        $this->checkPermission(PermissionService::READ);

        $eventStore->setLimit($limit, $start);
        $eventStore->setSortByExt($sort);

        return $this->returnSuccess($eventStore->getList(), $eventStore->getCount());
    }

    public function elements(EventRepository $eventRepository, int $eventId): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess();
    }

    /**
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function classNames(ClassNameStore $classNameStore): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess($classNameStore->getList());
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function methods(MethodStore $methodStore, string $describerClass): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $methodStore->setDescriberClass($describerClass);

        return $this->returnSuccess($methodStore->getList());
    }
}
