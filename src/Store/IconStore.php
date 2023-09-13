<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Model\Icon;
use mysqlDatabase;

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
        private string $iconTagTableName,
        mysqlDatabase $database = null,
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return Icon::class;
    }

    protected function initTable(): void
    {
        parent::initTable();

        if (count($this->tags) > 0) {
            $this->table->appendJoin(
                $this->iconTagTableName,
                '`' . $this->iconTagTableName . '`.`icon_id` = `' . $this->tableName . '`.`id`',
            );
        }
    }

    protected function setWheres(): void
    {
        if (count($this->tags) > 0) {
            $this->addWhere(
                '`' . $this->iconTagTableName . '`.`tag` IN (' . $this->table->getParametersString($this->tags) . ')',
                $this->tags,
            );
        }
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
