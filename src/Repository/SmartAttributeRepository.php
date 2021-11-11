<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Model\SmartAttribute;

/**
 * @method Drive[] fetchAll(string $where, array $parameters, string $modelClassName, int $limit = null, int $offset = null, string $orderBy = null)
 */
class SmartAttributeRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return Drive[]
     */
    public function getAll(): array
    {
        return $this->fetchAll('', [], SmartAttribute::class);
    }
}
