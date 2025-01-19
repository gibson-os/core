<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\EventController;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Store\Event\ElementStore;
use GibsonOS\Core\Store\EventStore;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class EventControllerTest extends FunctionalTest
{
    private EventController $eventController;

    protected function _before(): void
    {
        parent::_before();

        $this->eventController = $this->serviceManager->get(EventController::class);
    }

    public function testGet(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $eventDent = (new Event($this->modelWrapper))->setName('dent');
        $modelManager->saveWithoutChildren($eventDent);
        $eventArthur = (new Event($this->modelWrapper))->setName('arthur');
        $modelManager->saveWithoutChildren($eventArthur);

        $response = $this->eventController->get($this->serviceManager->get(EventStore::class));
        $body = json_decode($response->getBody(), true);
        $expect = [
            [
                'id' => $eventArthur->getId(),
                'name' => 'arthur',
                'active' => true,
                'async' => true,
                'exitOnError' => true,
                'lastRun' => null,
                'runtime' => null,
                'tags' => [],
                'lockCommand' => false,
            ],
            [
                'id' => $eventDent->getId(),
                'name' => 'dent',
                'active' => true,
                'async' => true,
                'exitOnError' => true,
                'lastRun' => null,
                'runtime' => null,
                'tags' => [],
                'lockCommand' => false,
            ],
        ];

        $this->assertEquals($expect, $body['data']);
    }

    public function testGetElements(): void
    {
        //        $modelManager = $this->serviceManager->get(ModelManager::class);
        //        $event = (new Event($this->modelWrapper))->setName('arthur');
        //        $modelManager->saveWithoutChildren($event);
        //        $childElement = (new Event\Element($this->modelWrapper))
        //            ->setEvent($event)
        //            ->setClass('zaphod')
        //            ->setMethod('bebblebrox')
        //        ;
        //        $element = (new Event\Element($this->modelWrapper))
        //            ->setEvent($event)
        //            ->setClass('ford')
        //            ->setMethod('prefect')
        //            ->addChildren([$childElement])
        //        ;
        //        $modelManager->save($element);
        //
        //        $response = $this->eventController->getElements(
        //            $this->serviceManager->get(ElementStore::class),
        //            $event,
        //        );
        //        $body = json_decode($response->getBody(), true);
    }
}
