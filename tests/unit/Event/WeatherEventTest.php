<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use Codeception\Test\Unit;
use GibsonOS\Core\Event\WeatherEvent;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;

class WeatherEventTest extends Unit
{
    use ModelManagerTrait;

    private EventService|ObjectProphecy $eventService;

    private ReflectionManager|ObjectProphecy $reflectionManager;

    private WeatherRepository|ObjectProphecy $weatherRepository;

    private WeatherEvent $weatherEvent;

    protected function _before(): void
    {
        $this->loadModelManager();
        $this->eventService = $this->prophesize(EventService::class);
        $this->reflectionManager = $this->prophesize(ReflectionManager::class);
        $this->weatherRepository = $this->prophesize(WeatherRepository::class);

        $this->weatherEvent = new WeatherEvent(
            $this->eventService->reveal(),
            $this->reflectionManager->reveal(),
            $this->weatherRepository->reveal(),
        );
    }

    public function testTemperature(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setTemperature(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->temperature($location));
    }

    public function testFeelsLike(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setFeelsLike(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->feelsLike($location));
    }

    public function testPressure(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setPressure(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->pressure($location));
    }

    public function testHumidity(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setHumidity(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->humidity($location));
    }

    public function testDevPoint(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setDewPoint(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->dewPoint($location));
    }

    public function testClouds(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setClouds(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->clouds($location));
    }

    public function testUvIndex(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setUvIndex(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->uvIndex($location));
    }

    public function testWindSpeed(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setWindSpeed(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->windSpeed($location));
    }

    public function testWindGust(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setWindGust(42.42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42.42, $this->weatherEvent->windGust($location));
    }

    public function testWindDegree(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setWindDegree(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->windDegree($location));
    }

    public function testVisibility(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setVisibility(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->visibility($location));
    }

    public function testRain(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setRain(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->rain($location));
    }

    public function testSnow(): void
    {
        $location = new Location($this->modelWrapper->reveal());
        $weather = (new Weather($this->modelWrapper->reveal()))->setSnow(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $this->weatherEvent->snow($location));
    }
}
