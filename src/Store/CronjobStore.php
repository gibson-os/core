<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Cronjob;

class CronjobStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Cronjob::class;
    }

    protected function getOrderMapping(): array
    {
        return [
            'command' => 'command',
            'user' => 'user',
            'last_run' => 'last_run',
            'active' => 'active',
        ];
    }
}
