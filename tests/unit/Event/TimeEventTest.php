<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use GibsonOS\Core\Event\TimeEvent;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Prophecy\ObjectProphecy;

class TimeEventTest extends UnitTest
{
    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before(): void
    {
        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->serviceManager->setService(DateTimeService::class, $this->dateTimeService->reveal());
    }

    public function testSleep(): void
    {
        $now = (new \DateTime())->getTimestamp();
        $timeEvent = $this->serviceManager->get(TimeEvent::class);
        $timeEvent->sleep(1);
        $this->assertEquals($now + 1, (new \DateTime())->getTimestamp());
    }

    public function testUsleep(): void
    {
        $now = (int) (new \DateTime())->format('u');
        $timeEvent = $this->serviceManager->get(TimeEvent::class);
        $timeEvent->usleep(42);
        $this->assertGreaterThanOrEqual($now + 42, (int) (new \DateTime())->format('u'));
    }

    public function testBetween(): void
    {
        $beforeYesterday = new \DateTime('-2 day');
        $yesterday = new \DateTime('-1 day');
        $tomorrow = new \DateTime('+1 day');
        $timeEvent = $this->serviceManager->get(TimeEvent::class);

        $this->dateTimeService->get()
            ->shouldBeCalledTimes(3)
            ->willReturn(new \DateTime())
        ;

        $this->assertTrue($timeEvent->between($yesterday, $tomorrow));
        $this->assertFalse($timeEvent->between($tomorrow, $yesterday));
        $this->assertFalse($timeEvent->between($beforeYesterday, $yesterday));
    }

    public function testGetDateParts(): void
    {
        $timeEvent = $this->serviceManager->get(TimeEvent::class);

        $this->dateTimeService->get()
            ->shouldBeCalledTimes(7)
            ->willReturn(new \DateTime('2020-02-20 21:22:23'))
        ;

        $this->assertEquals(2020, $timeEvent->year());
        $this->assertEquals(2, $timeEvent->month());
        $this->assertEquals(20, $timeEvent->dayOfMonth());
        $this->assertEquals(4, $timeEvent->dayOfWeek());
        $this->assertEquals(21, $timeEvent->hour());
        $this->assertEquals(22, $timeEvent->minute());
        $this->assertEquals(23, $timeEvent->second());
    }

    public function testIsDay(): void
    {
        $timeEvent = $this->serviceManager->get(TimeEvent::class);
        $dayDate = new \DateTime('2020-02-20 09:22:23');
        $nightDate = new \DateTime('2020-02-20 21:22:23');
        $this->dateTimeService->get()
            ->shouldBeCalledTimes(2)
            ->willReturn($dayDate, $nightDate)
        ;

        $this->dateTimeService->getSunrise($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;
        $this->dateTimeService->get('@42')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(24)
        ;
        $this->dateTimeService->get('@24')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertTrue($timeEvent->isDay());

        $this->dateTimeService->getSunrise($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(4242)
        ;
        $this->dateTimeService->get('@4242')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(2424)
        ;
        $this->dateTimeService->get('@2424')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertFalse($timeEvent->isDay());
    }

    public function testIsNight(): void
    {
        $timeEvent = $this->serviceManager->get(TimeEvent::class);
        $dayDate = new \DateTime('2020-02-20 09:22:23');
        $nightDate = new \DateTime('2020-02-20 21:22:23');
        $this->dateTimeService->get()
            ->shouldBeCalledTimes(2)
            ->willReturn($dayDate, $nightDate)
        ;

        $this->dateTimeService->getSunrise($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;
        $this->dateTimeService->get('@42')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(24)
        ;
        $this->dateTimeService->get('@24')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertFalse($timeEvent->isNight());

        $this->dateTimeService->getSunrise($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(4242)
        ;
        $this->dateTimeService->get('@4242')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(2424)
        ;
        $this->dateTimeService->get('@2424')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertTrue($timeEvent->isNight());
    }
}
