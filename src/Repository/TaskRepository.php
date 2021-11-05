<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;

/**
 * @method Task fetchOne(string $where, array $parameters, string $modelClassName)
 */
class TaskRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getByNameAndModuleId(string $name, int $moduleId): Task
    {
        return $this->fetchOne('`name`=? AND `module_id`=?', [$name, $moduleId], Task::class);
    }
}
