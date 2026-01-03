<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Event;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Event>
 */
class EventStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getModelClassName(): string
    {
        return Event::class;
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }

    #[Override]
    protected function getOrderMapping(): array
    {
        return [
            'name' => '`name`',
        ];
    }
}
