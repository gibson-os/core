<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;

class TaskRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByNameAndModuleId(string $name, int $moduleId): Task
    {
        return $this->fetchOne('`name`=? AND `module_id`=?', [$name, $moduleId], Task::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable(Task::getTableName());

        return $table
            ->setWhere('`ids` IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
