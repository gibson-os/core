<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Event;

use GibsonOS\Core\Event\WeatherEvent;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\WeatherRepository;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Prophecy\ObjectProphecy;

class WeatherEventTest extends UnitTest
{
    private WeatherRepository|ObjectProphecy $weatherRepository;

    protected function _before(): void
    {
        $this->weatherRepository = $this->prophesize(WeatherRepository::class);
        $this->serviceManager->setService(WeatherRepository::class, $this->weatherRepository->reveal());
    }

    public function testTemperature(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setTemperature(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->temperature($location));
    }

    public function testFeelsLike(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setFeelsLike(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->feelsLike($location));
    }

    public function testPressure(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setPressure(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->pressure($location));
    }

    public function testHumidity(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setHumidity(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->humidity($location));
    }

    public function testDevPoint(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setDewPoint(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->dewPoint($location));
    }

    public function testClouds(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setClouds(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->clouds($location));
    }

    public function testUvIndex(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setUvIndex(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->uvIndex($location));
    }

    public function testWindSpeed(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setWindSpeed(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->windSpeed($location));
    }

    public function testWindGust(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setWindGust(42.42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42.42, $weatherEvent->windGust($location));
    }

    public function testWindDegree(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setWindDegree(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->windDegree($location));
    }

    public function testVisibility(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setVisibility(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->visibility($location));
    }

    public function testRain(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setRain(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->rain($location));
    }

    public function testSnow(): void
    {
        $weatherEvent = $this->serviceManager->get(WeatherEvent::class);
        $location = new Location();
        $weather = (new Weather())->setSnow(42);
        $this->weatherRepository->getByNearestDate($location, null)
            ->shouldBeCalledOnce()
            ->willReturn($weather)
        ;

        $this->assertEquals(42, $weatherEvent->snow($location));
    }
}
