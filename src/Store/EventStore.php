<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Event;

class EventStore extends AbstractDatabaseStore
{
    protected function getTableName(): string
    {
        return Event::getTableName();
    }

    protected function getCountField(): string
    {
        return '`' . $this->getTableName() . '`.`id`';
    }

    protected function getOrderMapping(): array
    {
        return [
            'name' => '`' . $this->getTableName() . '`.`name`',
        ];
    }

    public function getList(): array
    {
        $this->table->setWhere($this->getWhere());
        $this->table->setOrderBy($this->getOrderBy());
        $this->table->select(
            false,
            '`' . $this->getTableName() . '`.`id`, ' .
            '`' . $this->getTableName() . '`.`name`, ' .
            '`' . $this->getTableName() . '`.`active`, ' .
            '`' . $this->getTableName() . '`.`async`, ' .
            '`' . $this->getTableName() . '`.`modified`'
        );

        return $this->table->connection->fetchAssocList();
    }
}
