<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Lock;

/**
 * @method Lock fetchOne(string $where, array $parameters, string $modelClassName)
 */
class LockRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByName(string $name): Lock
    {
        return $this->fetchOne('`name`=?', [$name], Lock::class);
    }
}
