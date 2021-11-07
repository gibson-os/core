<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Action;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

/**
 * @method Permission[] fetchAll(string $where, array $parameters, string $modelClassName, int $limit = null, int $offset = null, string $orderBy = null)
 */
class PermissionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return Permission[]
     */
    public function findByActionId(int $actionId): array
    {
        return $this->fetchAll('`action_id`=?', [$actionId], Permission::class);
    }

    public function deleteByAction(string $action): bool
    {
        $table = $this->getTable(Permission::getTableName())
            ->setWhere('`action`=?')
            ->addWhereParameter($action)
        ;

        return $table->deletePrepared();
    }
}
