<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Module;

/**
 * @extends AbstractDatabaseStore<Module>
 */
class ModuleStore extends AbstractDatabaseStore
{
    protected function getDefaultOrder(): string
    {
        return '`name`';
    }

    protected function getModelClassName(): string
    {
        return Module::class;
    }
}
