<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Icon;

use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Store\AbstractDatabaseStore;

/**
 * @extends AbstractDatabaseStore<Tag>
 */
class TagStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Tag::class;
    }

    protected function getCountField(): string
    {
        return '*';
    }

    protected function getDefaultOrder(): string
    {
        return '`tag`';
    }

    protected function initTable(): void
    {
        parent::initTable();
        $this->table->setGroupBy('`tag`');
    }

    /**
     * @return iterable<array>
     */
    public function getList(): iterable
    {
        $this->table->selectPrepared(false, '`tag`, COUNT(`icon_id`) AS `count`');

        return $this->table->connection->fetchAssocList();
    }
}
