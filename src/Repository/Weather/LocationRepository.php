<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Weather;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;

class LocationRepository extends AbstractRepository
{
    private DateTimeService $dateTimeService;

    public function __construct(DateTimeService $dateTimeService)
    {
        $this->dateTimeService = $dateTimeService;
    }

    public function getById(int $id): Location
    {
        $table = $this->getTable(Location::getTableName())
            ->setWhere('`id`=?')
            ->addWhereParameter($id)
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            throw (new SelectError())->setTable($table);
        }

        $location = new Location();
        $location->loadFromMysqlTable($table);

        return $location;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Location[]
     */
    public function getToUpdate(): array
    {
        $table = $this->getTable(Location::getTableName())
            ->setWhere(
                '`active`=1 AND ' .
                '(`last_run` IS NULL OR FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`) <= ?)'
            )
            ->addWhereParameter($this->dateTimeService->get()->format('Y-m-d H:i:s'))
        ;
        $locations = [];

        if (!$table->selectPrepared()) {
            return $locations;
        }

        do {
            $location = new Location();
            $location->loadFromMysqlTable($table);
            $locations[] = $location;
        } while ($table->next());

        return $locations;
    }
}
