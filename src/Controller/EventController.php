<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Event\Tag;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\Event\ClassNameStore;
use GibsonOS\Core\Store\Event\ClassTriggerStore;
use GibsonOS\Core\Store\Event\ElementStore;
use GibsonOS\Core\Store\Event\MethodStore;
use GibsonOS\Core\Store\Event\TriggerStore;
use GibsonOS\Core\Store\EventStore;
use JsonException;
use MDO\Client;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class EventController extends AbstractController
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    #[CheckPermission([Permission::READ])]
    public function get(
        EventStore $eventStore,
        int $start = 0,
        int $limit = 0,
        array $sort = [],
    ): AjaxResponse {
        $eventStore->setLimit($limit, $start);
        $eventStore->setSortByExt($sort);

        return $this->returnSuccess($eventStore->getList(), $eventStore->getCount());
    }

    /**
     * @throws ClientException
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function getElements(
        ElementStore $elementStore,
        #[GetModel(['id' => 'eventId'])]
        ?Event $event = null,
        ?string $node = null,
    ): AjaxResponse {
        if (
            $event === null
            || ($node !== null && $node !== 'NaN')
        ) {
            return $this->returnSuccess([]);
        }

        $elementStore->setEvent($event);

        return $this->returnSuccess($elementStore->getList());
    }

    /**
     * @throws GetError
     */
    #[CheckPermission([Permission::READ])]
    public function getClassNames(ClassNameStore $classNameStore): AjaxResponse
    {
        return $this->returnSuccess($classNameStore->getList());
    }

    /**
     * @param class-string $className
     *
     * @throws FactoryError
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::READ])]
    public function getMethods(MethodStore $methodStore, string $className): AjaxResponse
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
    #[CheckPermission([Permission::READ])]
    public function getClassTriggers(ClassTriggerStore $classTriggerStore, string $className): AjaxResponse
    {
        $classTriggerStore->setClassName($className);

        return $this->returnSuccess($classTriggerStore->getList());
    }

    /**
     * @throws ClientException
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function getTriggers(TriggerStore $triggerStore, #[GetModel(['id' => 'eventId'])] Event $event): AjaxResponse
    {
        $triggerStore->setEvent($event);

        return $this->returnSuccess($triggerStore->getList());
    }

    #[CheckPermission([Permission::WRITE])]
    public function post(
        EventRepository $eventRepository,
        ModelManager $modelManager,
        #[GetMappedModel]
        Event $event,
    ): AjaxResponse {
        $eventRepository->startTransaction();

        try {
            $modelManager->save($event);
        } catch (Exception $e) {
            $eventRepository->rollback();

            return $this->returnFailure($e->getMessage());
        }

        $eventRepository->commit();

        return $this->returnSuccess(['id' => $event->getId()]);
    }

    /**
     * @param Event[] $events
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postCopy(
        Client $client,
        ModelManager $modelManager,
        #[GetModels(Event::class)]
        array $events,
    ): AjaxResponse {
        $client->startTransaction();

        try {
            foreach ($events as $event) {
                $elements = $event->getElements();
                $triggers = $event->getTriggers();
                $tags = $event->getTags();

                $event
                    ->setId(null)
                    ->setName(sprintf('%s - Kopie', $event->getName()))
                    ->setActive(false)
                ;
                $modelManager->save($event);

                $event
                    ->setElements($this->removeElementIds($elements))
                    ->setTriggers(array_map(
                        fn (Trigger $trigger): Trigger => $trigger->setId(null),
                        $triggers,
                    ))
                    ->setTags(array_map(
                        fn (Tag $tag): Tag => $tag->setId(null),
                        $tags,
                    ))
                ;

                $modelManager->save($event);
            }
        } catch (Exception $exception) {
            $client->rollback();

            throw $exception;
        }

        $client->commit();

        return $this->returnSuccess($events[0]->getElements());
    }

    /**
     * @param Element[] $elements
     */
    private function removeElementIds(array $elements): array
    {
        return array_map(
            fn (Element $element): Element => $element
                ->setId(null)
                ->setChildren($this->removeElementIds($element->getChildren())),
            $elements,
        );
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws JsonException
     * @throws SaveError
     * @throws EventException
     * @throws ReflectionException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postRun(EventService $eventService, #[GetModel(['id' => 'eventId'])] Event $event): AjaxResponse
    {
        $eventService->runEvent($event, true);

        return $this->returnSuccess();
    }

    /**
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::DELETE])]
    public function delete(ModelManager $modelManager, #[GetModel(['id' => 'eventId'])] Event $event): AjaxResponse
    {
        $modelManager->delete($event);

        return $this->returnSuccess();
    }
}
