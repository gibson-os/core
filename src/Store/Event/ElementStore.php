<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Dto\Event\Element as ElementDto;
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
     * @return ElementDto[]
     */
    public function getList(): array
    {
        $this->where[] = '`event_id`=' . ($this->eventId ?? 0);
        $this->table->setWhere($this->getWhere());
        $this->table->setOrderBy('`left`');

        $data = [];
        $elements = [];

        if ($this->table->select(false) !== false) {
            throw (new SelectError())->setTable($this->table);
        }

        foreach ($this->table->connection->fetchAssocList() as $element) {
            $elementDto = (new ElementDto(
                (int) $element->id,
                $element->class,
                $element->method,
                (int) $element->left,
                (int) $element->right
            ))
                ->setReturns(unserialize($element->returns))
                ->setParameters(unserialize($element->parameters))
                ->setOperator($element->operator)
                ->setCommand($element->command)
            ;
            $elements[$elementDto->getId()] = $elementDto;

            if ($element->parent_id === null) {
                $data[] = $elementDto;
            } else {
                $elements[$element->parent_id]->addChildren($elementDto);
            }
        }

        return $data;
    }
}
