<?php declare(strict_types=1);

namespace GibsonOS\Core\Repository\User;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\PermissionView;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Service\PermissionService;
use stdClass;

class PermissionViewRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     *
     * @return stdClass[]
     */
    public function getTaskList(?int $userId, string $module = null): array
    {
        $table = $this->getTable(PermissionView::getTableName());
        $table
            ->setWhere(
                '(`user_id`=? OR `user_id`=0) AND ' .
                '`permission`>? AND ' .
                '`task_id` IS NOT NULL' .
                ($module === null ? '' : ' AND `module`=?')
            )
            ->setWhereParameters([$userId ?? 0, PermissionService::DENIED])
        ;

        if ($module !== null) {
            $table->addWhereParameter($module);
        }

        if (!$table->selectPrepared(false, 'DISTINCT `module`, `task_name` AS `task`')) {
            throw (new SelectError())->setTable($table);
        }

        return $table->connection->fetchObjectList();
    }
}
