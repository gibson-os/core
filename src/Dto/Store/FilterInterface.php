<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Store;

use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Query\Where;

interface FilterInterface
{
    public function getWhere(string $field, array $values, DatabaseStoreWrapper $databaseStoreWrapper): Where;
}
