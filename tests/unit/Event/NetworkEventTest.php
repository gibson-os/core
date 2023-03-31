<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use Codeception\Test\Unit;
use GibsonOS\Core\Event\NetworkEvent;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\NetworkService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class NetworkEventTest extends Unit
{
    use ProphecyTrait;

    private EventService|ObjectProphecy $eventService;

    private ReflectionManager|ObjectProphecy $reflectionManager;

    private NetworkService|ObjectProphecy $networkService;

    private NetworkEvent $networkEvent;

    protected function _before(): void
    {
        $this->eventService = $this->prophesize(EventService::class);
        $this->reflectionManager = $this->prophesize(ReflectionManager::class);
        $this->networkService = $this->prophesize(NetworkService::class);

        $this->networkEvent = new NetworkEvent(
            $this->eventService->reveal(),
            $this->reflectionManager->reveal(),
            $this->networkService->reveal(),
        );
    }

    public function testPing(): void
    {
        $this->networkService->ping('galaxy', 42)
            ->shouldBeCalledTimes(2)
            ->willReturn(true, false)
        ;

        $this->assertTrue($this->networkEvent->ping('galaxy', 42));
        $this->assertFalse($this->networkEvent->ping('galaxy', 42));
    }
}
