<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\SmartAttribute;

readonly class SmartAttributeRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return SmartAttribute[]
     */
    public function getAll(): array
    {
        return $this->fetchAll('', [], SmartAttribute::class);
    }
}
