<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Cronjob;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Cronjob>
 */
class CronjobStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getModelClassName(): string
    {
        return Cronjob::class;
    }

    #[Override]
    protected function getOrderMapping(): array
    {
        return [
            'command' => 'command',
            'user' => 'user',
            'last_run' => 'last_run',
            'active' => 'active',
        ];
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`command`' => OrderDirection::ASC];
    }
}
