<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Weather;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class LocationRepository extends AbstractRepository
{
    public function __construct(
        private DateTimeService $dateTimeService,
        private LoggerInterface $logger,
        #[GetTableName(Location::class)] private string $locationTableName
    ) {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Location
    {
        $table = $this->getTable($this->locationTableName)
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

    public function findByName(string $name, bool $onlyActive): array
    {
        $this->logger->debug(sprintf('Find weather location with name %d', $name));

        $table = $this->getTable($this->locationTableName);
        $where = '`name` LIKE ?';

        if ($onlyActive) {
            $where .= ' AND `active`=1';
        }

        $table
            ->setWhere($where)
            ->addWhereParameter($name . '%')
        ;

        if (!$table->selectPrepared()) {
            return [];
        }

        $models = [];

        do {
            $model = new Location();
            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }

    /**
     * @return Location[]
     */
    public function getToUpdate(): array
    {
        $table = $this->getTable($this->locationTableName)
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
