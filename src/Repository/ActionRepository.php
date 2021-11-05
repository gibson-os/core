<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;

/**
 * @method Action fetchOne(string $where, array $parameters, string $modelClassName)
 */
class ActionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByNameAndTaskId(string $name, int $taskId): Action
    {
        return $this->fetchOne('`name`=? AND `task_id`=?', [$name, $taskId], Action::class);
    }
}
