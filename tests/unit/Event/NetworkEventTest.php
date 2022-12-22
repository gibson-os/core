<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Event;

use GibsonOS\Core\Event\NetworkEvent;
use GibsonOS\Core\Service\NetworkService;
use GibsonOS\UnitTest\AbstractTest;
use Prophecy\Prophecy\ObjectProphecy;

class NetworkEventTest extends AbstractTest
{
    private NetworkService|ObjectProphecy $networkService;

    protected function _before(): void
    {
        $this->networkService = $this->prophesize(NetworkService::class);
        $this->serviceManager->setService(NetworkService::class, $this->networkService->reveal());
    }

    public function testPing(): void
    {
        $this->networkService->ping('galaxy', 42)
            ->shouldBeCalledTimes(2)
            ->willReturn(true, false)
        ;

        $networkEvent = $this->serviceManager->get(NetworkEvent::class);

        $this->assertTrue($networkEvent->ping('galaxy', 42));
        $this->assertFalse($networkEvent->ping('galaxy', 42));
    }
}
