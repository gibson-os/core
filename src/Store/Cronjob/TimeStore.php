<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Cronjob;

use GibsonOS\Core\Model\Cronjob\Time;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class TimeStore extends AbstractDatabaseStore
{
    /**
     * @var int|null
     */
    private $cronjobId;

    protected function getTableName(): string
    {
        return Time::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    public function getList(): array
    {
        if (!empty($this->cronjobId)) {
            $this->table->setWhere('`cronjob_id`=' . $this->cronjobId);
        }

        $this->table->select(false);

        return $this->table->connection->fetchAssocList();
    }

    public function setCronjobId(?int $cronjobId): TimeStore
    {
        $this->cronjobId = $cronjobId;

        return $this;
    }
}
