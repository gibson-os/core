<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Icon;

use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use MDO\Dto\Record;
use MDO\Dto\Value;
use MDO\Enum\OrderDirection;
use Override;

/**
 * @extends AbstractDatabaseStore<Tag>
 */
class TagStore extends AbstractDatabaseStore
{
    #[Override]
    protected function getModelClassName(): string
    {
        return Tag::class;
    }

    #[Override]
    protected function getCountField(): string
    {
        return '*';
    }

    #[Override]
    protected function getDefaultOrder(): array
    {
        return ['`tag`' => OrderDirection::ASC];
    }

    #[Override]
    protected function initQuery(): void
    {
        parent::initQuery();

        $this->selectQuery
            ->setSelects(['tag' => '`tag`', 'count' => 'COUNT(`icon_id`)'])
            ->setGroupBy(['`tag`'])
        ;
    }

    #[Override]
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
