<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Drive;

class DriveStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Drive::class;
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table->appendJoin(Drive\Stat::getTableName(), '`system_drive`.`id`=`system_drive_stat`.`drive_id`');
    }

    protected function setWheres(): void
    {
        $this->addWhere('UNIX_TIMESTAMP(`system_drive_stat`.`added`)>=UNIX_TIMESTAMP(NOW())-900');
    }
}
