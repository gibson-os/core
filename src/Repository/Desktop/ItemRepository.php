<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Desktop;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Desktop\Item;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\AbstractRepository;

readonly class ItemRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Item::class)] private string $itemTableName)
    {
    }

    public function deleteByIdsNot(User $user, array $ids): bool
    {
        $table = $this->getTable($this->itemTableName);

        return $table
            ->setWhere('`id` NOT IN (' . $table->getParametersString($ids) . ') AND `user_id`=?')
            ->setWhereParameters($ids)
            ->addWhereParameter($user->getId() ?? 0)
            ->deletePrepared()
        ;
    }

    /**
     * @throws SelectError
     */
    public function getLastPosition(User $user): Item
    {
        return $this->fetchOne(
            '`user_id`=?',
            [$user->getId() ?? 0],
            Item::class,
            '`position` DESC',
        );
    }

    public function updatePosition(User $user, int $fromPosition, int $increase): bool
    {
        return $this->getTable($this->itemTableName)
            ->setWhere('`user_id`=? AND `position`>=?')
            ->setWhereParameters([$increase, $user->getId() ?? 0, $fromPosition])
            ->update('`position`=`position`+?')
        ;
    }

    /**
     * @throws SelectError
     *
     * @return Item[]
     */
    public function getByUser(User $user): array
    {
        return $this->fetchAll(
            '`user_id`=?',
            [$user->getId() ?? 0],
            Item::class,
            orderBy: '`position` ASC',
        );
    }
}
