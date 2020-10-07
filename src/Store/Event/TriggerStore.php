<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event\Trigger;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class TriggerStore extends AbstractDatabaseStore
{
    /**
     * @var int
     */
    private $eventId;

    protected function getTableName(): string
    {
        return Trigger::getTableName();
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    /**
     * @throws SelectError
     */
    public function getList(): iterable
    {
        $this->table
            ->setWhere('`event_id`=?')
            ->addWhereParameter($this->eventId)
            ->setOrderBy('priority')
        ;

        if (!$this->table->selectPrepared(false)) {
            throw (new SelectError())->setTable($this->table);
        }

        return $this->table->connection->fetchAssocList();
    }

    public function setEventId(int $eventId): TriggerStore
    {
        $this->eventId = $eventId;

        return $this;
    }
}
