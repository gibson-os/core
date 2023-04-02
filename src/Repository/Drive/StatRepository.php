<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Drive;

use GibsonOS\Core\Model\Drive\Stat;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;

class StatRepository extends AbstractRepository
{
    public function __construct(private readonly DateTimeService $dateTimeService)
    {
    }

    /**
     * @return array{min: int, max: int}
     */
    public function getTimeRange(): array
    {
        $range = $this->getAggregate('MIN(`added`) AS `min`, MAX(`added`) AS `max`', Stat::class);

        return [
            'min' => empty($range['min']) ? 0 : $this->dateTimeService->get((string) $range['min'])->getTimestamp(),
            'max' => empty($range['max']) ? 0 : $this->dateTimeService->get((string) $range['max'])->getTimestamp(),
        ];
    }
}
