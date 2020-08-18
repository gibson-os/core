<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Event;

use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class ElementStore extends AbstractDatabaseStore
{
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

    public function getList(): array
    {
        return [];
    }
}
