<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Action;

use GibsonOS\Core\Model\Action\Permission;
use GibsonOS\Core\Repository\AbstractRepository;

class PermissionRepository extends AbstractRepository
{
    public function deleteByAction(string $action): bool
    {
        $table = $this->getTable(Permission::getTableName())
            ->setWhere('`action`=?')
            ->addWhereParameter($action)
        ;

        return $table->deletePrepared();
    }
}
