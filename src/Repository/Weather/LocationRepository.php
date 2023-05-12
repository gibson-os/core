<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Weather;

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
    ) {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Location
    {
        return $this->fetchOne('`id`=?', [$id], Location::class);
    }

    /**
     * @throws SelectError
     */
    public function findByName(string $name, bool $onlyActive): array
    {
        $this->logger->debug(sprintf('Find weather location with name %d', $name));

        $where = '`user` LIKE ?';
        $parameters = [$name . '%'];

        if ($onlyActive) {
            $where .= ' AND `active`=?';
            $parameters[] = 1;
        }

        return $this->fetchAll($where, $parameters, Location::class);
    }

    /**
     * @throws SelectError
     *
     * @return Location[]
     */
    public function getToUpdate(): array
    {
        return $this->fetchAll(
            '`active`=1 AND ' .
            '(`last_run` IS NULL OR FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`) <= ?)',
            [$this->dateTimeService->get()->format('Y-m-d H:i:s')],
            Location::class,
        );
    }
}
