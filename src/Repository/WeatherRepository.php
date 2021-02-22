<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeZone;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\DateTimeService;

class WeatherRepository extends AbstractRepository
{
    private DateTimeService $dateTimeService;

    public function __construct(DateTimeService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    public function getCurrent(Location $location): Weather
    {
        $table = $this->getTable(Weather::getTableName())
            ->setWhere('`location_id`=? AND date<=?')
            ->setWhereParameters([
                $location->getId(),
                $this->dateTimeService->get('now', new DateTimeZone($location->getTimezone()))->format('Y-m-d H:i:s'),
            ])
            ->setLimit(1)
            ->setOrderBy('`date` DESC')
        ;
    }
}
