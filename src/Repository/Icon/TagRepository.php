<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository\Icon;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Repository\AbstractRepository;

readonly class TagRepository extends AbstractRepository
{
    public function __construct(#[GetTableName(Tag::class)] private string $tagTableName)
    {
    }

    public function deleteByIconId(int $iconId): bool
    {
        $table = $this->getTable($this->tagTableName)
            ->setWhere('`icon_id`=?')
            ->addWhereParameter($iconId)
        ;

        return $table->deletePrepared();
    }
}
