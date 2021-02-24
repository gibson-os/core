<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use DateTimeZone;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
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

    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByDate(Location $location, DateTimeInterface $date): Weather
    {
        $table = $this->getTable(Weather::getTableName())
            ->setWhere('`location_id`=? AND date=?')
            ->setWhereParameters([
                $location->getId(),
                $date->format('Y-m-d H:i:s'),
            ])
        ;

        if (!$table->selectPrepared()) {
            throw (new SelectError())->setTable($table);
        }

        $weather = new Weather();
        $weather->loadFromMysqlTable($table);

        return $weather;
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

        if (!$table->selectPrepared()) {
            throw (new SelectError())->setTable($table);
        }

        $weather = new Weather();
        $weather->loadFromMysqlTable($table);

        return $weather;
    }

    public function deleteBetweenDates(Location $location, DateTimeInterface $from, DateTimeInterface $to): void
    {
        $this->getTable(Weather::getTableName())
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
