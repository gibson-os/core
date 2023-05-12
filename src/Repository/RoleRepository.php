<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Role;

readonly class RoleRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByName(string $name): Role
    {
        return $this->fetchOne('`name`=?', [$name], Role::class);
    }
}
