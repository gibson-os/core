<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
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
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\ClassTriggerStore;
use GibsonOS\Core\Store\Event\ElementStore;
use GibsonOS\Core\Store\Event\MethodStore;
use GibsonOS\Core\Store\Event\TriggerStore;
use GibsonOS\Core\Store\EventStore;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionException;

class EventController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(
        EventStore $eventStore,
        int $start = 0,
        int $limit = 0,
        array $sort = []
    ): AjaxResponse {
        $eventStore->setLimit($limit, $start);
        $eventStore->setSortByExt($sort);

        return $this->returnSuccess($eventStore->getList(), $eventStore->getCount());
    }

    /**
     * @throws GetError
     * @throws JsonException
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
     * @param class-string $className
     *
     * @throws FactoryError
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::READ)]
    public function methods(MethodStore $methodStore, string $className): AjaxResponse
    {
        $methodStore->setClassName($className);

        return $this->returnSuccess($methodStore->getList());
    }

    /**
     * @param class-string $className
     *
     * @throws FactoryError
     * @throws ReflectionException
     */
    #[CheckPermission(Permission::READ)]
    public function classTriggers(ClassTriggerStore $classTriggerStore, string $className): AjaxResponse
    {
        $classTriggerStore->setClassName($className);

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
        #[GetMappedModel] Event $event
    ): AjaxResponse {
        $eventRepository->startTransaction();

//        errlog(JsonUtility::encode($event->getElements()));
//
//        return $this->returnSuccess(['id' => $event->getId(), 'event' => $event]);

        try {
            $event->save();
        } catch (Exception $e) {
            $eventRepository->rollback();

            return $this->returnFailure($e->getMessage());
        }

        $eventRepository->commit();

        return $this->returnSuccess(['id' => $event->getId()]);
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws JsonException
     * @throws SaveError
     * @throws EventException
     */
    #[CheckPermission(Permission::WRITE)]
    public function run(EventService $eventService, #[GetModel(['id' => 'eventId'])] $event): AjaxResponse
    {
        $eventService->runEvent($event, true);

        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     * @throws DeleteError
     */
    #[CheckPermission(Permission::DELETE)]
    public function delete(#[GetModel(['id' => 'eventId'])] $event): AjaxResponse
    {
        $event->delete();

        return $this->returnSuccess();
    }
}
