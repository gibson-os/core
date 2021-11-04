<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;

/**
 * @method Module fetchOne(string $where, array $parameters, string $modelClassName)
 */
class ModuleRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByName(string $name): Module
    {
        return $this->fetchOne('`name`=?', [$name], Module::class);
    }
}
