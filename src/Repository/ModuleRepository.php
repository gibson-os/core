<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;

class ModuleRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByName(string $name): Module
    {
        return $this->fetchOne('`name`=?', [$name], Module::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable(Module::getTableName());

        return $table
            ->setWhere('`ids` IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
