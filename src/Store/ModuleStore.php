<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Module;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Module>
 */
class ModuleStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }

    #[Override]
    protected function getModelClassName(): string
    {
        return Module::class;
    }
}
