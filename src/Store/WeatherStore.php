<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use DateTimeInterface;
use GibsonOS\Core\Model\Weather;
use Override;

/**
 * @extends AbstractDatabaseStore<Weather>
 */
class WeatherStore extends AbstractDatabaseStore
{
    private int $locationId;

    private ?DateTimeInterface $date = null;

    #[Override]
    protected function getModelClassName(): string
    {
        return Weather::class;
    }

    #[Override]
    protected function setWheres(): void
    {
        $this->addWhere('`location_id`=?', [$this->locationId]);

        if ($this->date instanceof DateTimeInterface) {
            $this->addWhere('`date`>', [$this->date->format('Y-m-d H:i:s')]);
        }
    }

    public function setLocationId(int $locationId): WeatherStore
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function setDate(?DateTimeInterface $date): WeatherStore
    {
        $this->date = $date;

        return $this;
    }
}
