<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Cronjob;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;
use GibsonOS\Core\Repository\AbstractRepository;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;

class TimeRepository extends AbstractRepository
{
    /**
     * @throws ClientException
     * @throws SelectError
     * @throws RecordException
     */
    public function hasTimes(Cronjob $cronjob): bool
    {
        $aggregations = $this->getAggregations(
            ['aggregations' => 'COUNT(`id`)'],
            Cronjob\Time::class,
            '`cronjob_id`=?',
            [$cronjob->getId() ?? 0],
        );

        $count = $aggregations->get('count')->getValue();

        return $count !== null && $count > 0;
    }
}
