<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Cronjob;

use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Repository\AbstractRepository;

class TimeRepository extends AbstractRepository
{
    public function hasTimes(Cronjob $cronjob): bool
    {
        $aggregates = $this->getAggregate(
            'COUNT(`id`)',
            Cronjob\Time::class,
            '`cronjob_id`=?',
            [$cronjob->getId() ?? 0],
        );

        return ($aggregates === null ? null : ($aggregates[0] ?? 0)) > 0;
    }
}
