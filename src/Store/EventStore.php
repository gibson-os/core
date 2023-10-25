<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Event;
use MDO\Enum\OrderDirection;

/**
 * @extends AbstractDatabaseStore<Event>
 */
class EventStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Event::class;
    }

    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }

    protected function getOrderMapping(): array
    {
        return [
            'name' => '`name`',
        ];
    }
}
