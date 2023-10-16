<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use Codeception\Test\Unit;
use GibsonOS\Core\Event\EventEvent;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EventEventTest extends Unit
{
    use ModelManagerTrait;

    private EventService|ObjectProphecy $eventService;

    private ReflectionManager|ObjectProphecy $reflectionManager;

    private EventEvent $eventEvent;

    protected function _before(): void
    {
        $this->loadModelManager();
        $this->eventService = $this->prophesize(EventService::class);
        $this->reflectionManager = $this->prophesize(ReflectionManager::class);

        $this->eventEvent = new EventEvent(
            $this->eventService->reveal(),
            $this->reflectionManager->reveal(),
            $this->modelManager->reveal(),
        );
    }

    public function testActivate(): void
    {
        $event = (new Event($this->modelWrapper->reveal()))->setActive(false);
        $this->assertFalse($event->isActive());

        $this->modelManager->saveWithoutChildren($event)
            ->shouldBeCalledOnce()
        ;

        $this->eventEvent->activate($event);
        $this->assertTrue($event->isActive());
    }

    public function testIsActive(): void
    {
        $event = (new Event($this->modelWrapper->reveal()))->setActive(true);
        $this->assertTrue($event->isActive());
        $this->assertTrue($this->eventEvent->isActive($event));

        $event->setActive(false);
        $this->assertFalse($event->isActive());
        $this->assertFalse($this->eventEvent->isActive($event));
    }

    public function testDeactivate(): void
    {
        $event = (new Event($this->modelWrapper->reveal()))->setActive(true);
        $this->assertTrue($event->isActive());

        $this->modelManager->saveWithoutChildren($event)
            ->shouldBeCalledOnce()
        ;

        $this->eventEvent->deactivate($event);
        $this->assertFalse($event->isActive());
    }

    public function testStart(): void
    {
        $event = new Event($this->modelWrapper->reveal());

        $this->eventService->runEvent($event, false)
            ->shouldBeCalledOnce()
        ;
        $this->eventEvent->start($event, false);

        $this->eventService->runEvent($event, true)
            ->shouldBeCalledOnce()
        ;
        $this->eventEvent->start($event, true);
    }

    public function testStop(): void
    {
        $event = new Event($this->modelWrapper->reveal());

        $this->eventService->stop($event)
            ->shouldBeCalledOnce()
        ;
        $this->eventEvent->stop($event);
    }
}
