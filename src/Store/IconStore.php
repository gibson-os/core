<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Icon;

class IconStore extends AbstractDatabaseStore
{
    /**
     * @var string[]
     */
    private array $tags = [];

    protected function getModelClassName(): string
    {
        return Icon::class;
    }

    protected function initTable(): void
    {
        parent::initTable();

        if (count($this->tags) > 0) {
            $this->table->appendJoin(Icon\Tag::getTableName(), '`icon_tag`.`icon_id` = `icon`.`id`');
        }
    }

    protected function setWheres(): void
    {
        if (count($this->tags) > 0) {
            $this->addWhere(
                '`icon_tag`.`tag` IN (' . $this->table->getParametersString($this->tags) . ')',
                $this->tags
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
