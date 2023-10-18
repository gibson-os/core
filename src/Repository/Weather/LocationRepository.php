<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Weather;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Exception\ClientException;
use Psr\Log\LoggerInterface;
use ReflectionException;

class LocationRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        private readonly DateTimeService $dateTimeService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getById(int $id): Location
    {
        return $this->fetchOne('`id`=?', [$id], Location::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function findByName(string $name, bool $onlyActive): array
    {
        $this->logger->debug(sprintf('Find weather location with name %d', $name));

        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($onlyActive) {
            $where .= ' AND `active`=?';
            $parameters[] = 1;
        }

        return $this->fetchAll($where, $parameters, Location::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Location[]
     */
    public function getToUpdate(): array
    {
        return $this->fetchAll(
            '`active`=? AND ' .
            '(`last_run` IS NULL OR FROM_UNIXTIME(UNIX_TIMESTAMP(`last_run`)+`interval`) <= ?)',
            [1, $this->dateTimeService->get()->format('Y-m-d H:i:s')],
            Location::class,
        );
    }
}
