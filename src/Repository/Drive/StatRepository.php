<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Drive;

use GibsonOS\Core\Model\Drive\Stat;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use MDO\Exception\ClientException;

class StatRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        private readonly DateTimeService $dateTimeService,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     *
     * @return array{min: int, max: int}
     */
    public function getTimeRange(): array
    {
        $aggregations = $this->getAggregations(['min' => 'MIN(`added`)', 'max' => 'MAX(`added`)'], Stat::class);
        $min = $aggregations->get('min')->getValue();
        $max = $aggregations->get('max')->getValue();

        return [
            'min' => $min === null ? 0 : $this->dateTimeService->get((string) $min)->getTimestamp(),
            'max' => $max === null ? 0 : $this->dateTimeService->get((string) $max)->getTimestamp(),
        ];
    }
}
