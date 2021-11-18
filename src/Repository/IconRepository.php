<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Icon;

class IconRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getById(int $id): Icon
    {
        return $this->fetchOne('`id`=?', [$id], Icon::class);
    }

    public function findByIds(array $ids): array
    {
        return $this->fetchAll(
            '`id` IN (' . $this->getTable(Icon::getTableName())->getParametersString($ids) . ')',
            $ids,
            Icon::class
        );
    }

    public function deleteByIds(array $ids): bool
    {
        $table = $this->getTable(Icon::class);
        $table
            ->setWhere('`id` IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
        ;

        return $table->deletePrepared();
    }
}
