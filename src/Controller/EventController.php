<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use DateTime;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\ElementStore;
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

    public function elements(ElementStore $elementStore, int $eventId): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $elementStore->setEventId($eventId);

        return $this->returnSuccess($elementStore->getList());
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

    public function save(string $name, bool $active, bool $async, array $elements): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);

        $event = (new Event())
            ->setName($name)
            ->setActive($active)
            ->setAsync($async)
            ->setModified(new DateTime())
        ;
        $event->save();
        $this->saveElements($event, $elements);

        return $this->returnSuccess();
    }

    private function saveElements(Event $event, array $elements, int $left = 0, int $parentId = null): int
    {
        foreach ($elements as $element) {
            $elementModel = (new Event\Element())
                ->setEvent($event)
                ->setParentId($parentId)
                ->setClass($element['className'])
                ->setMethod($element['method'])
                ->setCommand($element['command'] ?: null)
                ->setOperator($element['operator'] ?: null)
                ->setParameters(empty($element['parameters']) ? null : serialize($element['parameters']))
                ->setReturns(empty($element['returns']) ? null : serialize($element['returns']))
                ->setLeft($left)
                ->setRight($left++)
            ;
            $elementModel->save();
            $elementModel->setRight($this->saveElements($event, $element['children'], $left, $elementModel->getId()));
            $elementModel->save();
        }

        return $left;
    }
}
