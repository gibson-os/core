<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use DateTime;
use Exception;
use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\ClassTriggerStore;
use GibsonOS\Core\Store\Event\ElementStore;
use GibsonOS\Core\Store\Event\MethodStore;
use GibsonOS\Core\Store\Event\TriggerStore;
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

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws DateTimeError
     */
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

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function classTriggers(ClassTriggerStore $classTriggerStore, string $describerClass): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $classTriggerStore->setDescriberClass($describerClass);

        return $this->returnSuccess($classTriggerStore->getList());
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function triggers(TriggerStore $triggerStore, int $eventId): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $triggerStore->setEventId($eventId);

        return $this->returnSuccess($triggerStore->getList());
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function save(
        EventRepository $eventRepository,
        string $name,
        bool $active,
        bool $async,
        array $elements,
        array $triggers,
        int $eventId = null
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);

        $eventRepository->startTransaction();

        try {
            $event = new Event();

            if (!empty($eventId)) {
                $event = $eventRepository->getById($eventId);
            }

            $event
                ->setName($name)
                ->setActive($active)
                ->setAsync($async)
                ->setModified(new DateTime())
            ;
            $event->save();
            $eventRepository->deleteElements(
                $event,
                $eventRepository->saveElements($event, $elements)
            );
            $eventRepository->deleteTriggers(
                $event,
                $eventRepository->saveTriggers($event, $triggers)
            );
        } catch (Exception $e) {
            $eventRepository->rollback();

            return $this->returnFailure($e->getMessage());
        }

        $eventRepository->commit();

        return $this->returnSuccess(['id' => $event->getId()]);
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function run(EventService $eventService, EventRepository $eventRepository, int $eventId): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);

        $eventService->runEvent($eventRepository->getById($eventId), true);

        return $this->returnSuccess();
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws FactoryError
     */
    public function autoComplete(
        ServiceManagerService $serviceManagerService,
        RequestService $requestService,
        string $autoCompleteClassname,
        int $id = null,
        string $name = null
    ): AjaxResponse {
        $this->checkPermission(PermissionService::READ);

        /** @var AutoCompleteInterface $autoComplete */
        $autoComplete = $serviceManagerService->get($autoCompleteClassname);
        $parameters = $requestService->getRequestValues();

        if ($id !== null) {
            return $this->returnSuccess($autoComplete->getById($id, $parameters));
        }

        return $this->returnSuccess($autoComplete->getByNamePart($name ?? '', $parameters));
    }
}
