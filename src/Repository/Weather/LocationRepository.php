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

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Location[]
     */
    public function getToUpdate(): array
    {
        $table = $this->getTable(Location::getTableName())
            ->setWhere('`active`=1 AND FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`)<=?')
            ->addWhereParameter($this->dateTimeService->get()->format('Y-m-d H:i:s'))
        ;

        if (!$table->select()) {
            throw (new SelectError())->setTable($table);
        }

        $locations = [];

        do {
            $location = new Location();
            $location->loadFromMysqlTable($table);
            $locations[] = $location;
        } while ($table->next());

        return $locations;
    }
}
