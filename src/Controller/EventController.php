<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use DateTime;
use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\ClassTriggerStore;
use GibsonOS\Core\Store\Event\ElementStore;
use GibsonOS\Core\Store\Event\MethodStore;
use GibsonOS\Core\Store\Event\TriggerStore;
use GibsonOS\Core\Store\EventStore;
use JsonException;

class EventController extends AbstractController
{
    /**
     * @throws GetError
     */
    #[CheckPermission(Permission::READ)]
    public function index(
        EventStore $eventStore,
        int $start = 0,
        int $limit = 0,
        array $sort = []
    ): ResponseInterface {
        $eventStore->setLimit($limit, $start);
        $eventStore->setSortByExt($sort);

        return $this->returnSuccess($eventStore->getList(), $eventStore->getCount());
    }

    /**
     * @throws GetError
     * @throws SelectError
     * @throws FactoryError
     */
    #[CheckPermission(Permission::READ)]
    public function elements(ElementStore $elementStore, int $eventId = null, string $node = null): AjaxResponse
    {
        if (
            $eventId === null ||
            ($node !== null && $node !== 'NaN')
        ) {
            return $this->returnSuccess([]);
        }

        $elementStore->setEventId($eventId);

        return $this->returnSuccess($elementStore->getList());
    }

    /**
     * @throws GetError
     */
    #[CheckPermission(Permission::READ)]
    public function classNames(ClassNameStore $classNameStore): AjaxResponse
    {
        return $this->returnSuccess($classNameStore->getList());
    }

    /**
     * @throws FactoryError
     */
    #[CheckPermission(Permission::READ)]
    public function methods(MethodStore $methodStore, string $describerClass): AjaxResponse
    {
        $methodStore->setDescriberClass($describerClass);

        return $this->returnSuccess($methodStore->getList());
    }

    /**
     * @throws FactoryError
     */
    #[CheckPermission(Permission::READ)]
    public function classTriggers(ClassTriggerStore $classTriggerStore, string $describerClass): AjaxResponse
    {
        $classTriggerStore->setDescriberClass($describerClass);

        return $this->returnSuccess($classTriggerStore->getList());
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws SelectError
     * @throws JsonException
     */
    #[CheckPermission(Permission::READ)]
    public function triggers(TriggerStore $triggerStore, int $eventId): AjaxResponse
    {
        $triggerStore->setEventId($eventId);

        return $this->returnSuccess($triggerStore->getList());
    }

    #[CheckPermission(Permission::WRITE)]
    public function save(
        EventRepository $eventRepository,
        string $name,
        bool $active,
        bool $async,
        array $elements,
        array $triggers,
        int $eventId = null
    ): AjaxResponse {
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
     * @throws JsonException
     * @throws SelectError
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function run(EventService $eventService, EventRepository $eventRepository, int $eventId): AjaxResponse
    {
        $eventService->runEvent($eventRepository->getById($eventId), true);

        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     * @throws DeleteError
     */
    #[CheckPermission(Permission::DELETE)]
    public function delete(EventRepository $eventRepository, int $eventId): AjaxResponse
    {
        $event = $eventRepository->getById($eventId);
        $event->delete();

        return $this->returnSuccess();
    }
}
