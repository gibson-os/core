<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Icon;

/**
 * @method Icon fetchOne(string $where, array $parameters, string $modelClassName)
 */
class IconRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     */
    public function getById(int $id): Icon
    {
        return $this->fetchOne('`id`=?', [$id], Icon::class);
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
