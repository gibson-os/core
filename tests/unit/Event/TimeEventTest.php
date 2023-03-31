<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Event\TimeEvent;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\EventService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TimeEventTest extends Unit
{
    use ProphecyTrait;

    private EventService|ObjectProphecy $eventService;

    private ReflectionManager|ObjectProphecy $reflectionManager;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private TimeEvent $timeEvent;

    protected function _before(): void
    {
        $this->eventService = $this->prophesize(EventService::class);
        $this->reflectionManager = $this->prophesize(ReflectionManager::class);
        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->timeEvent = new TimeEvent(
            $this->eventService->reveal(),
            $this->reflectionManager->reveal(),
            $this->dateTimeService->reveal(),
        );
    }

    public function testSleep(): void
    {
        $now = (new DateTime())->getTimestamp();
        $this->timeEvent->sleep(1);
        $this->assertEquals($now + 1, (new DateTime())->getTimestamp());
    }

    public function testUsleep(): void
    {
        $now = (int) (new DateTime())->format('u');
        $this->timeEvent->usleep(42);
        $this->assertGreaterThanOrEqual($now + 42, (int) (new DateTime())->format('u'));
    }

    public function testBetween(): void
    {
        $beforeYesterday = new DateTime('-2 day');
        $yesterday = new DateTime('-1 day');
        $tomorrow = new DateTime('+1 day');

        $this->dateTimeService->get()
            ->shouldBeCalledTimes(3)
            ->willReturn(new DateTime())
        ;

        $this->assertTrue($this->timeEvent->between($yesterday, $tomorrow));
        $this->assertFalse($this->timeEvent->between($tomorrow, $yesterday));
        $this->assertFalse($this->timeEvent->between($beforeYesterday, $yesterday));
    }

    public function testGetDateParts(): void
    {
        $this->dateTimeService->get()
            ->shouldBeCalledTimes(7)
            ->willReturn(new DateTime('2020-02-20 21:22:23'))
        ;

        $this->assertEquals(2020, $this->timeEvent->year());
        $this->assertEquals(2, $this->timeEvent->month());
        $this->assertEquals(20, $this->timeEvent->dayOfMonth());
        $this->assertEquals(4, $this->timeEvent->dayOfWeek());
        $this->assertEquals(21, $this->timeEvent->hour());
        $this->assertEquals(22, $this->timeEvent->minute());
        $this->assertEquals(23, $this->timeEvent->second());
    }

    public function testIsDay(): void
    {
        $dayDate = new DateTime('2020-02-20 09:22:23');
        $nightDate = new DateTime('2020-02-20 21:22:23');
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
            ->willReturn(new DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(24)
        ;
        $this->dateTimeService->get('@24')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertTrue($this->timeEvent->isDay());

        $this->dateTimeService->getSunrise($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(4242)
        ;
        $this->dateTimeService->get('@4242')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(2424)
        ;
        $this->dateTimeService->get('@2424')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertFalse($this->timeEvent->isDay());
    }

    public function testIsNight(): void
    {
        $dayDate = new DateTime('2020-02-20 09:22:23');
        $nightDate = new DateTime('2020-02-20 21:22:23');
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
            ->willReturn(new DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($dayDate)
            ->shouldBeCalledOnce()
            ->willReturn(24)
        ;
        $this->dateTimeService->get('@24')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertFalse($this->timeEvent->isNight());

        $this->dateTimeService->getSunrise($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(4242)
        ;
        $this->dateTimeService->get('@4242')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 06:00:00'))
        ;
        $this->dateTimeService->getSunset($nightDate)
            ->shouldBeCalledOnce()
            ->willReturn(2424)
        ;
        $this->dateTimeService->get('@2424')
            ->shouldBeCalledOnce()
            ->willReturn(new DateTime('2020-02-20 18:00:00'))
        ;
        $this->assertTrue($this->timeEvent->isNight());
    }
}
