<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Icon;

use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use MDO\Dto\Record;
use MDO\Dto\Value;

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

    protected function initQuery(): void
    {
        parent::initQuery();

        $this->selectQuery
            ->setSelects(['tag' => '`tag`', 'count' => 'COUNT(`icon_id`)'])
            ->setGroupBy(['`tag`'])
        ;
    }

    public function getList(): array
    {
        $result = $this->getDatabaseStoreWrapper()->getClient()->execute($this->selectQuery);

        return array_map(
            static fn (Record $record): array => array_map(
                static fn (Value $value): float|string|int|null => $value->getValue(),
                $record->getValues(),
            ),
            iterator_to_array($result->iterateRecords()),
        );
    }
}
