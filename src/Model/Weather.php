<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTime;
use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Model\Weather\Location;
use JsonSerializable;
use mysqlDatabase;

class Weather extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private int $locationId = 0;

    private DateTimeInterface $date;

    private float $temperature = 0.0;

    private float $feelsLike = 0.0;

    private int $pressure = 0;

    private int $humidity = 0;

    private float $dewPoint = 0.0;

    private int $clouds = 0;

    private float $uvIndex = 0.0;

    private float $windSpeed = 0.0;

    private int $windDegree = 0;

    private int $visibility = 0;

    private ?float $probabilityOfPrecipitation = 0.0;

    private ?string $description = null;

    private ?float $rain = null;

    private ?float $snow = null;

    private ?float $windGust = null;

    private ?string $icon = null;

    private Location $location;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);
        $this->date = new DateTime();
        $this->location = new Location();
    }

    public static function getTableName(): string
    {
        return 'weather';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Weather
    {
        $this->id = $id;

        return $this;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function setLocationId(int $locationId): Weather
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): Weather
    {
        $this->date = $date;

        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): Weather
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getFeelsLike(): float
    {
        return $this->feelsLike;
    }

    public function setFeelsLike(float $feelsLike): Weather
    {
        $this->feelsLike = $feelsLike;

        return $this;
    }

    public function getPressure(): int
    {
        return $this->pressure;
    }

    public function setPressure(int $pressure): Weather
    {
        $this->pressure = $pressure;

        return $this;
    }

    public function getHumidity(): int
    {
        return $this->humidity;
    }

    public function setHumidity(int $humidity): Weather
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getDewPoint(): float
    {
        return $this->dewPoint;
    }

    public function setDewPoint(float $dewPoint): Weather
    {
        $this->dewPoint = $dewPoint;

        return $this;
    }

    public function getClouds(): int
    {
        return $this->clouds;
    }

    public function setClouds(int $clouds): Weather
    {
        $this->clouds = $clouds;

        return $this;
    }

    public function getUvIndex(): float
    {
        return $this->uvIndex;
    }

    public function setUvIndex(float $uvIndex): Weather
    {
        $this->uvIndex = $uvIndex;

        return $this;
    }

    public function getWindSpeed(): float
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(float $windSpeed): Weather
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    public function getWindDegree(): int
    {
        return $this->windDegree;
    }

    public function setWindDegree(int $windDegree): Weather
    {
        $this->windDegree = $windDegree;

        return $this;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): Weather
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getProbabilityOfPrecipitation(): ?float
    {
        return $this->probabilityOfPrecipitation;
    }

    public function setProbabilityOfPrecipitation(?float $probabilityOfPrecipitation): Weather
    {
        $this->probabilityOfPrecipitation = $probabilityOfPrecipitation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Weather
    {
        $this->description = $description;

        return $this;
    }

    public function getRain(): ?float
    {
        return $this->rain;
    }

    public function setRain(?float $rain): Weather
    {
        $this->rain = $rain;

        return $this;
    }

    public function getSnow(): ?float
    {
        return $this->snow;
    }

    public function setSnow(?float $snow): Weather
    {
        $this->snow = $snow;

        return $this;
    }

    public function getWindGust(): ?float
    {
        return $this->windGust;
    }

    public function setWindGust(?float $windGust): Weather
    {
        $this->windGust = $windGust;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): Weather
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function getLocation(): Location
    {
        $this->loadForeignRecord($this->location, $this->getLocationId());

        return $this->location;
    }

    public function setLocation(Location $location): Weather
    {
        $this->location = $location;
        $this->setLocationId($location->getId() ?? 0);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'locationId' => $this->getLocationId(),
            'date' => $this->getDate()->format('Y-m-d H:i:s'),
            'temperature' => $this->getTemperature(),
            'feelsLike' => $this->getFeelsLike(),
            'pressure' => $this->getPressure(),
            'humidity' => $this->getHumidity(),
            'dewPoint' => $this->getDewPoint(),
            'clouds' => $this->getClouds(),
            'uvIndex' => $this->getUvIndex(),
            'windSpeed' => $this->getWindSpeed(),
            'windDegree' => $this->getWindDegree(),
            'visibility' => $this->getVisibility(),
            'probabilityOfPrecipitation' => $this->getProbabilityOfPrecipitation(),
            'description' => $this->getDescription(),
            'rain' => $this->getRain(),
            'snow' => $this->getSnow(),
            'windGust' => $this->getWindGust(),
            'icon' => $this->getIcon(),
        ];
    }
}
