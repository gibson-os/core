<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Cronjob;

class CronjobStore extends AbstractDatabaseStore
{
    protected function getTableName(): string
    {
        return Cronjob::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [
            'command' => 'command',
            'user' => 'user',
            'last_run' => 'last_rund',
            'active' => 'active',
        ];
    }

    public function getList(): array
    {
        if ($this->table->select(false) !== false) {
            throw (new SelectError())->setTable($this->table);
        }

        return $this->table->connection->fetchAssocList();
    }
}
