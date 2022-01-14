<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Icon;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Icon;
use JsonSerializable;
use mysqlDatabase;

#[Table]
class Tag extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $iconId;

    #[Column(length: 64, primary: true)]
    private string $tag;

    private Icon $icon;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->icon = new Icon();
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
