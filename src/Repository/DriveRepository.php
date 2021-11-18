<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Drive;

class DriveRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return Drive[]
     */
    public function getDrivesWithAttributes(int $secondsWithAttributes = 900): array
    {
        $table = $this->getTable(Drive::getTableName())
            ->appendJoin(Drive\Stat::getTableName(), '`system_drive_stat`.`drive_id`=`system_drive`.`id`')
            ->setWhere('UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-?')
            ->addWhereParameter($secondsWithAttributes)
        ;

        return $this->getModels($table, Drive::class);
    }
}
