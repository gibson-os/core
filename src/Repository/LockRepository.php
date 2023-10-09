<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Lock;
use MDO\Exception\ClientException;

class LockRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws ClientException
     */
    public function getByName(string $name): Lock
    {
        return $this->fetchOne('`name`=?', [$name], Lock::class);
    }
}
