<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class ElementStore extends AbstractDatabaseStore
{
    /**
     * @var int|null
     */
    private $eventId;

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    protected function getTableName(): string
    {
        return Element::getTableName();
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
     * @throws \GibsonOS\Core\Exception\DateTimeError
     *
     * @return Element[]
     */
    public function getList(): array
    {
        $this->where[] = '`event_id`=' . ($this->eventId ?? 0);
        $this->table->setWhere($this->getWhere());
        $this->table->setOrderBy('`parent_id`, `order`');

        $data = [];
        $models = [];

        if (!$this->table->select()) {
            throw (new SelectError())->setTable($this->table);
        }

        do {
            $model = new Element();
            $model->loadFromMysqlTable($this->table);
            $models[$model->getId() ?? 0] = $model;
            $parentId = $model->getParentId();

            if ($parentId === null) {
                $data[] = $model;
            } else {
                $models[$parentId]->addChildren($model);
            }
        } while ($this->table->next());

        return $data;
    }
}
