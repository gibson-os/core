<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Module;
use MDO\Enum\OrderDirection;

/**
 * @extends AbstractDatabaseStore<Module>
 */
class ModuleStore extends AbstractDatabaseStore
{
    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }

    protected function getModelClassName(): string
    {
        return Module::class;
    }
}
