<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use GibsonOS\Core\Event\EventEvent;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class EventEventTest extends UnitTest
{
    private EventService|ObjectProphecy $eventService;

    protected function _before(): void
    {
        $this->eventService = $this->prophesize(EventService::class);
        $this->serviceManager->setService(EventService::class, $this->eventService->reveal());
    }

    public function testActivate(): void
    {
        $eventEvent = $this->serviceManager->get(EventEvent::class);
        $event = (new Event())->setActive(false);
        $this->assertFalse($event->isActive());

        $this->modelManager->saveWithoutChildren(Argument::Any())
            ->shouldBeCalledOnce()
        ;

        $eventEvent->activate($event);
        $this->assertTrue($event->isActive());
    }

    public function testIsActive(): void
    {
        $eventEvent = $this->serviceManager->get(EventEvent::class);

        $event = (new Event())->setActive(true);
        $this->assertTrue($event->isActive());
        $this->assertTrue($eventEvent->isActive($event));

        $event->setActive(false);
        $this->assertFalse($event->isActive());
        $this->assertFalse($eventEvent->isActive($event));
    }

    public function testDeactivate(): void
    {
        $eventEvent = $this->serviceManager->get(EventEvent::class);
        $event = (new Event())->setActive(true);
        $this->assertTrue($event->isActive());

        $this->modelManager->saveWithoutChildren(Argument::Any())
            ->shouldBeCalledOnce()
        ;

        $eventEvent->deactivate($event);
        $this->assertFalse($event->isActive());
    }

    public function testStart(): void
    {
        $event = new Event();
        $eventEvent = $this->serviceManager->get(EventEvent::class);

        $this->eventService->runEvent($event, false)
            ->shouldBeCalledOnce()
        ;
        $eventEvent->start($event, false);

        $this->eventService->runEvent($event, true)
            ->shouldBeCalledOnce()
        ;
        $eventEvent->start($event, true);
    }

    public function testStop(): void
    {
        $event = new Event();
        $eventEvent = $this->serviceManager->get(EventEvent::class);

        $this->eventService->stop($event)
            ->shouldBeCalledOnce()
        ;
        $eventEvent->stop($event);
    }
}
