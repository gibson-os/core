<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Module;

class ModuleStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Module::class;
    }
}
