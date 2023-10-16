<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Query\Join;
use MDO\Dto\Table;

/**
 * @extends AbstractDatabaseStore<Icon>
 */
class IconStore extends AbstractDatabaseStore
{
    /**
     * @var string[]
     */
    private array $tags = [];

    public function __construct(
        #[GetTable(Icon\Tag::class)]
        private readonly Table $iconTagTable,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Icon::class;
    }

    protected function getAlias(): ?string
    {
        return 'i';
    }

    protected function initQuery(): void
    {
        parent::initQuery();

        if (count($this->tags) === 0) {
            return;
        }

        $this->selectQuery->addJoin(new Join($this->iconTagTable, 'it', '`it`.`icon_id`=`i`.`id`'));
    }

    protected function setWheres(): void
    {
        if (count($this->tags) === 0) {
            return;
        }

        $this->addWhere(
            sprintf('`it`.`tag` IN (%s)', $this->getDatabaseStoreWrapper()->getSelectService()->getParametersString($this->tags)),
            $this->tags,
        );
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): IconStore
    {
        $this->tags = $tags;

        return $this;
    }
}
