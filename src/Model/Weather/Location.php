<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Weather;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\AutoCompleteModelInterface;

#[Table]
#[Key(unique: true, columns: ['latitude', 'longitude'])]
#[Key(columns: ['interval', 'active'])]
class Location extends AbstractModel implements \JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 128)]
    #[Key(true)]
    private string $name;

    #[Column]
    private float $latitude;

    #[Column]
    private float $longitude;

    #[Column(length: 128)]
    private string $timezone;

    #[Column]
    private int $interval;

    #[Column]
    private bool $active = false;

    #[Column]
    private ?\DateTimeInterface $lastRun = null;

    #[Column(length: 255)]
    private ?string $error = null;

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

    public function getLastRun(): ?\DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?\DateTimeInterface $lastRun): Location
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): Location
    {
        $this->error = $error;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $lastRun = $this->getLastRun();

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'timezone' => $this->getTimezone(),
            'active' => $this->isActive(),
            'lastRun' => $lastRun?->format('Y-m-d H:i:s'),
            'error' => $this->getError(),
        ];
    }

    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
