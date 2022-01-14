<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Drive;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Drive\Stat;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\DateTimeService;

class StatRepository extends AbstractRepository
{
    public function __construct(
        private DateTimeService $dateTimeService,
        #[GetTableName(Stat::class)] private string $statTableName
    ) {
    }

    /**
     * @return array{min: int, max: int}
     */
    public function getTimeRange(): array
    {
        $table = $this->getTable($this->statTableName);
        $range = $table->selectAggregatePrepared('MIN(`added`) AS `min`, MAX(`added`) AS `max`');

        return [
            'min' => empty($range['min']) ? 0 : $this->dateTimeService->get((string) $range['min'])->getTimestamp(),
            'max' => empty($range['max']) ? 0 : $this->dateTimeService->get((string) $range['max'])->getTimestamp(),
        ];
    }
}
