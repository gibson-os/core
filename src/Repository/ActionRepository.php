<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;

class ActionRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Action::class)] private readonly string $actionTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Action
    {
        return $this->fetchOne('`id`=?', [$id], Action::class);
    }

    /**
     * @throws SelectError
     *
     * @return Action[]
     */
    public function findByName(string $name, int $taskId = null): array
    {
        $where = '`name` LIKE ?';
        $parameters = [$name . '%'];

        if ($taskId !== null) {
            $where .= ' AND `task_id`=?';
            $parameters[] = $taskId;
        }

        return $this->fetchAll($where, $parameters, Action::class);
    }

    /**
     * @throws SelectError
     */
    public function getByNameAndTaskId(string $name, int $taskId): Action
    {
        return $this->fetchOne('`name`=? AND `task_id`=?', [$name, $taskId], Action::class);
    }

    public function deleteByIdsNot(array $ids): bool
    {
        $table = $this->getTable($this->actionTableName);

        return $table
            ->setWhere('`id` NOT IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
            ->deletePrepared()
        ;
    }
}
