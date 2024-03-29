<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Icon;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Icon;
use JsonSerializable;

/**
 * @method Icon getIcon()
 * @method Tag  setIcon(Icon $icon)
 */
#[Table]
class Tag extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $iconId;

    #[Column(length: 64, primary: true)]
    #[Key]
    private string $tag;

    #[Constraint]
    protected Icon $icon;

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

    public function jsonSerialize(): array
    {
        return [
            'iconId' => $this->getIconId(),
            'tag' => $this->getTag(),
        ];
    }
}
