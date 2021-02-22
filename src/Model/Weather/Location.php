<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Weather;

use DateTimeInterface;
use GibsonOS\Core\Model\AbstractModel;

class Location extends AbstractModel
{
    private ?int $id = null;

    private string $name = '';

    private float $latitude = 0.0;

    private float $longitude = 0.0;

    private string $timezone = '';

    private int $interval = 0;

    private bool $active = false;

    private ?DateTimeInterface $lastRun = null;

    public static function getTableName(): string
    {
        return 'weather_location';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Location
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Location
    {
        $this->name = $name;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): Location
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): Location
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): Location
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): Location
    {
        $this->interval = $interval;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Location
    {
        $this->active = $active;

        return $this;
    }

    public function getLastRun(): ?DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?DateTimeInterface $lastRun): Location
    {
        $this->lastRun = $lastRun;

        return $this;
    }
}
