<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;

class ModuleRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Module::class)] private string $moduleTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getByName(string $name): Module
    {
        return $this->fetchOne('`name`=?', [$name], Module::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable($this->moduleTableName);

        return $table
            ->setWhere('`ids` IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
