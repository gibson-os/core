<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Icon;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use MDO\Dto\Query\Join;
use Override;

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
        #[GetTableName(Icon\Tag::class)]
        private readonly string $iconTagTableName,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    #[Override]
    protected function getModelClassName(): string
    {
        return Icon::class;
    }

    #[Override]
    protected function getAlias(): ?string
    {
        return 'i';
    }

    #[Override]
    protected function initQuery(): void
    {
        parent::initQuery();

        if ($this->tags === []) {
            return;
        }

        $this->selectQuery->addJoin(new Join($this->getTable($this->iconTagTableName), 'it', '`it`.`icon_id`=`i`.`id`'));
    }

    #[Override]
    protected function setWheres(): void
    {
        if ($this->tags === []) {
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
