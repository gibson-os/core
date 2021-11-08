<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Icon;

use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Repository\AbstractRepository;

class TagRepository extends AbstractRepository
{
    public function deleteByIconId(int $iconId): bool
    {
        $table = $this->getTable(Tag::getTableName())
            ->setWhere('`icon_id`=?')
            ->addWhereParameter($iconId)
        ;

        return $table->deletePrepared();
    }
}
