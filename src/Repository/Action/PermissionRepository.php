<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Action;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Action\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

class PermissionRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Permission::class)] private readonly string $permissionTableName)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Permission[]
     */
    public function findByActionId(int $actionId): array
    {
        return $this->fetchAll('`action_id`=?', [$actionId], Permission::class);
    }

    public function deleteByAction(Action $action): bool
    {
        $table = $this->getTable($this->permissionTableName)
            ->setWhere('`action_id`=?')
            ->addWhereParameter($action->getId())
        ;

        return $table->deletePrepared();
    }
}
