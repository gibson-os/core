<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event\Event;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Tag as EventTag;
use JsonSerializable;
use Override;

/**
 * @method Tag      setEvent(Element $element)
 * @method Event    getEvent()
 * @method Tag      setTag(EventTag $tag)
 * @method EventTag getTag()
 */
#[Table]
#[Key(unique: true, columns: ['event_id', 'tag_id'])]
class Tag extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $eventId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $tagId;

    #[Constraint]
    protected Event $event;

    #[Constraint]
    protected EventTag $tag;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Tag
    {
        $this->id = $id;

        return $this;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): Tag
    {
        $this->eventId = $eventId;

        return $this;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function setTagId(int $tagId): Tag
    {
        $this->tagId = $tagId;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'tag' => $this->getTag(),
        ];
    }
}
