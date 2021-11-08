<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Icon;

use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Icon;
use JsonSerializable;
use mysqlDatabase;

class Tag extends AbstractModel implements JsonSerializable
{
    private int $iconId = 0;

    private string $tag = '';

    private Icon $icon;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->icon = new Icon();
    }

    public static function getTableName(): string
    {
        return 'icon_tag';
    }

    public function getIconId(): int
    {
        return $this->iconId;
    }

    public function setIconId(int $iconId): Tag
    {
        $this->iconId = $iconId;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): Tag
    {
        $this->tag = $tag;

        return $this;
    }

    public function getIcon(): Icon
    {
        $this->loadForeignRecord($this->icon, $this->getIconId());

        return $this->icon;
    }

    public function setIcon(Icon $icon): Tag
    {
        $this->icon = $icon;
        $this->setIconId($icon->getId() ?? 0);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'iconId' => $this->getIconId(),
            'tag' => $this->getTag(),
        ];
    }
}
