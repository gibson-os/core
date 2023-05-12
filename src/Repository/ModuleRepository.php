<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;

readonly class ModuleRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Module::class)] private string $moduleTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Module
    {
        return $this->fetchOne('`id`=?', [$id], Module::class);
    }

    /**
     * @throws SelectError
     *
     * @return Module[]
     */
    public function findByName(string $name): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        return $this->fetchAll($where, $parameters, Module::class);
    }

    /**
     * @throws SelectError
     */
    public function getByName(string $name): Module
    {
        return $this->fetchOne('`name`=?', [$name], Module::class);
    }

    /**
     * @throws SelectError
     *
     * @return Module[]
     */
    public function getAll(): array
    {
        return $this->fetchAll('', [], Module::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable($this->moduleTableName);

        return $table
            ->setWhere('`id` NOT IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
