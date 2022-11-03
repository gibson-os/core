<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\DateTimeService;

class WeatherRepository extends AbstractRepository
{
    public function __construct(
        private DateTimeService $dateTimeService,
        #[GetTableName(Weather::class)] private string $weatherTableName
    ) {
    }

    /**
     * @throws SelectError
     */
    public function getByDate(Location $location, \DateTimeInterface $date): Weather
    {
        return $this->fetchOne(
            '`location_id`=? AND date=?',
            [$location->getId(), $date->format('Y-m-d H:i:s')],
            Weather::class
        );
    }

    /**
     * @throws SelectError
     */
    public function getByNearestDate(Location $location, \DateTimeInterface $dateTime = null): Weather
    {
        if ($dateTime === null) {
            $dateTime = $this->dateTimeService->get('now', new \DateTimeZone($location->getTimezone()));
        }

        return $this->fetchOne(
            '`location_id`=? AND date<=?',
            [$location->getId(), $dateTime->format('Y-m-d H:i:s')],
            Weather::class
        );
    }

    public function deleteBetweenDates(Location $location, \DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $this->getTable($this->weatherTableName)
            ->setWhere('`date` > ? AND `date` < ? AND `location_id`=?')
            ->setWhereParameters([
                $from->format('Y-m-d H:i:s'),
                $to->format('Y-m-d H:i:s'),
                $location->getId(),
            ])
            ->deletePrepared()
        ;
    }
}
