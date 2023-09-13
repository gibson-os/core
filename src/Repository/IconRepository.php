<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Icon;

class IconRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Icon::class)] private string $iconTableName)
    {
    }

    /**
     * @throws SelectError
     */
    public function getById(int $id): Icon
    {
        return $this->fetchOne('`id`=?', [$id], Icon::class);
    }

    /**
     * @throws SelectError
     */
    public function findByIds(array $ids): array
    {
        return $this->fetchAll(
            '`id` IN (' . $this->getTable($this->iconTableName)->getParametersString($ids) . ')',
            $ids,
            Icon::class,
        );
    }

    public function deleteByIds(array $ids): bool
    {
        $table = $this->getTable($this->iconTableName);
        $table
            ->setWhere('`id` IN (' . $table->getParametersString($ids) . ')')
            ->setWhereParameters($ids)
        ;

        return $table->deletePrepared();
    }
}
