<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;

readonly class TaskRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Task::class)] private string $taskTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Task
    {
        return $this->fetchOne('`id`=?', [$id], Task::class);
    }

    /**
     * @throws SelectError
     *
     * @return Task[]
     */
    public function findByName(string $name, int $moduleId = null): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($moduleId !== null) {
            $where .= ' AND `module_id`=?';
            $parameters[] = $moduleId;
        }

        return $this->fetchAll($where, $parameters, Task::class);
    }

    /**
     * @throws SelectError
     */
    public function getByNameAndModuleId(string $name, int $moduleId): Task
    {
        return $this->fetchOne('`name`=? AND `module_id`=?', [$name, $moduleId], Task::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable($this->taskTableName);

        return $table
            ->setWhere('`id` NOT IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
