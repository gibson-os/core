<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTime;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonSerializable;

/**
 * @method Location getLocation()
 * @method Weather  setLocation(Location $location)
 */
#[Table]
#[Key(unique: true, columns: ['location_id', 'date'])]
class Weather extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $locationId;

    #[Column]
    private DateTimeInterface $date;

    #[Column]
    private float $temperature;

    #[Column]
    private float $feelsLike;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pressure;

    #[Column(type: Column::TYPE_TINYINT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $humidity;

    #[Column]
    private float $dewPoint;

    #[Column(type: Column::TYPE_TINYINT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $clouds;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private float $uvIndex;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private float $windSpeed;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $windDegree;

    #[Column(type: Column::TYPE_INT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $visibility;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?float $probabilityOfPrecipitation;

    #[Column(length: 255)]
    private ?string $description = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?float $rain = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?float $snow = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?float $windGust = null;

    #[Column(length: 512)]
    private ?string $icon = null;

    #[Column]
    private ?DateTimeInterface $sunset = null;

    #[Column]
    private ?DateTimeInterface $sunrise = null;

    #[Constraint]
    protected Location $location;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->date = new DateTime();
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

    public function getSunset(): ?DateTimeInterface
    {
        return $this->sunset;
    }

    public function setSunset(?DateTimeInterface $sunset): Weather
    {
        $this->sunset = $sunset;

        return $this;
    }

    public function getSunrise(): ?DateTimeInterface
    {
        return $this->sunrise;
    }

    public function setSunrise(?DateTimeInterface $sunrise): Weather
    {
        $this->sunrise = $sunrise;

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
            'sunset' => $this->getSunset(),
            'sunrise' => $this->getSunrise(),
        ];
    }
}
