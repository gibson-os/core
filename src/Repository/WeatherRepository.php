<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use DateTimeInterface;
use DateTimeZone;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Weather;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Where;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\DeleteQuery;
use ReflectionException;

class WeatherRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        private readonly DateTimeService $dateTimeService,
        #[GetTableName(Weather::class)]
        private readonly string $weatherTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws SelectError
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getByDate(Location $location, DateTimeInterface $date): Weather
    {
        return $this->fetchOne(
            '`location_id`=? AND `date`=?',
            [$location->getId(), $date->format('Y-m-d H:i:s')],
            Weather::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByNearestDate(Location $location, DateTimeInterface $dateTime = null): Weather
    {
        if ($dateTime === null) {
            $dateTime = $this->dateTimeService->get('now', new DateTimeZone($location->getTimezone()));
        }

        return $this->fetchOne(
            '`location_id`=? AND `date`<=?',
            [$location->getId(), $dateTime->format('Y-m-d H:i:s')],
            Weather::class,
            ['`date`' => OrderDirection::DESC],
        );
    }

    public function deleteBetweenDates(Location $location, DateTimeInterface $from, DateTimeInterface $to): bool
    {
        $deleteQuery = (new DeleteQuery($this->getTable($this->weatherTableName)))
            ->addWhere(new Where('`location_id`=?', [$location->getId()]))
            ->addWhere(new Where('`date`>?', [$from->format('Y-m-d H:i:s')]))
            ->addWhere(new Where('`date`<?', [$to->format('Y-m-d H:i:s')]))
        ;

        try {
            $this->getRepositoryWrapper()->getClient()->execute($deleteQuery);
        } catch (ClientException) {
            return false;
        }

        return true;
    }
}
