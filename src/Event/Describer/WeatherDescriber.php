<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\Describer;

use GibsonOS\Core\AutoComplete\Weather\LocationAutoComplete;
use GibsonOS\Core\Dto\Event\Describer\Method;
use GibsonOS\Core\Dto\Event\Describer\Parameter\DateParameter;
use GibsonOS\Core\Dto\Event\Describer\Parameter\FloatParameter;
use GibsonOS\Core\Dto\Event\Describer\Parameter\IntParameter;
use GibsonOS\Core\Dto\Event\Describer\Parameter\StringParameter;
use GibsonOS\Core\Dto\Event\Describer\Parameter\Weather\LocationParameter;
use GibsonOS\Core\Dto\Event\Describer\Trigger;
use GibsonOS\Core\Event\WeatherEvent;

class WeatherDescriber implements DescriberInterface
{
    private LocationAutoComplete $locationAutoComplete;

    private LocationParameter $locationParameter;

    public function __construct(LocationAutoComplete $locationAutoComplete)
    {
        $this->locationAutoComplete = $locationAutoComplete;
        $this->locationParameter = new LocationParameter($this->locationAutoComplete);
    }

    public function getTitle(): string
    {
        return 'Wetter';
    }

    public function getTriggers(): array
    {
        return [
            'beforeLoad' => (new Trigger('Vor dem laden'))
                ->setParameters(['location' => $this->locationParameter]),
            'afterLoad' => (new Trigger('Nach dem laden'))
                ->setParameters([
                    'location' => $this->locationParameter,
                    'id' => new IntParameter('ID'),
                    'date' => new DateParameter('Datum'),
                    'temperature' => new FloatParameter('Temperature'),
                    'feelsLike' => new FloatParameter('Gefühlte Temperature'),
                    'pressure' => new IntParameter('Luftdruck'),
                    'humidity' => new IntParameter('Luftfeuchtigkeit'),
                    'dewPoint' => new FloatParameter('Taupunkt'),
                    'clouds' => new IntParameter('Wolken'),
                    'uvIndex' => new IntParameter('UV Index'),
                    'windSpeed' => new IntParameter('Wind Geschwindigkeit'),
                    'windDegree' => new IntParameter('Wind Richtung'),
                    'visibility' => new IntParameter('Sichtweite'),
                    'probabilityOfPrecipitation' => new FloatParameter('Regenwahrscheinlichkeit'),
                    'description' => new StringParameter('Beschreibung'),
                    'rain' => new StringParameter('Regen'),
                    'snow' => new StringParameter('Schnee'),
                    'windGust' => new StringParameter('Wind Böen'),
                    'icon' => new StringParameter('Icon'),
                ]),
        ];
    }

    public function getMethods(): array
    {
        $parameters = [
            'location' => $this->locationParameter,
            'date' => new DateParameter('Datum'),
        ];

        return [
            'temperature' => (new Method('Temperatur'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('Grad Celsius')]),
            'feelsLike' => (new Method('Gefühlte Temperatur'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('Grad Celsius')]),
            'pressure' => (new Method('Luftdruck'))
                ->setParameters($parameters)
                ->setReturns(['value' => new IntParameter('Luftdruck')]),
            'humidity' => (new Method('Luftfeuchtigkeit'))
                ->setParameters($parameters)
                ->setReturns(['value' => (new IntParameter('in Prozent'))->setRange(0, 100)]),
            'dewPoint' => (new Method('Taupunkt'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('Grad Celsius')]),
            'clouds' => (new Method('Wolken'))
                ->setParameters($parameters)
                ->setReturns(['value' => (new IntParameter('in Prozent'))->setRange(0, 100)]),
            'uvIndex' => (new Method('UV Index'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('UV Index')]),
            'windSpeed' => (new Method('Wind Geschwindigkeit'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('m/s')]),
            'windGust' => (new Method('Wind Böen'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('m/s')]),
            'windDegree' => (new Method('Wind Richtung'))
                ->setParameters($parameters)
                ->setReturns(['value' => new IntParameter('Winkel')]),
            'visibility' => (new Method('Sichtweite'))
                ->setParameters($parameters)
                ->setReturns(['value' => new IntParameter('Meter')]),
            'rain' => (new Method('Regen'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('mm in der letzten Stunde')]),
            'snow' => (new Method('Schnee'))
                ->setParameters($parameters)
                ->setReturns(['value' => new FloatParameter('mm in der letzten Stunde')]),
        ];
    }

    public function getEventClassName(): string
    {
        return WeatherEvent::class;
    }
}