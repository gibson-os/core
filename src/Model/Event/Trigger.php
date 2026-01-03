<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use JsonException;
use JsonSerializable;
use Override;

/**
 * @method Event   getEvent()
 * @method Trigger setEvent(Event $event)
 */
#[Table]
#[Key(unique: true, columns: ['event_id', 'trigger', 'weekday', 'day', 'month', 'year', 'hour', 'minute', 'second'])]
class Trigger extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $eventId;

    /**
     * @var class-string
     */
    #[Column(length: 512)]
    private string $class;

    #[Column(length: 64)]
    #[Key]
    private string $trigger;

    /**
     * Required for store.
     */
    private string $classTitle = '';

    /**
     * Required for store.
     */
    private string $triggerTitle = '';

    #[Column]
    private array $parameters = [];

    #[Column(type: Column::TYPE_TINYINT, length: 1, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $weekday = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $day = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $month = null;

    #[Column(type: Column::TYPE_SMALLINT, length: 4, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $year = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $hour = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $minute = null;

    #[Column(type: Column::TYPE_TINYINT, length: 2, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $second = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    #[Key]
    private ?int $priority = null;

    #[Constraint]
    protected Event $event;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Trigger
    {
        $this->id = $id;

        return $this;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): Trigger
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param class-string $class
     */
    public function setClass(string $class): Trigger
    {
        $this->class = $class;

        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): Trigger
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getClassTitle(): string
    {
        return $this->classTitle;
    }

    public function setClassTitle(string $classTitle): Trigger
    {
        $this->classTitle = $classTitle;

        return $this;
    }

    public function getTriggerTitle(): string
    {
        return $this->triggerTitle;
    }

    public function setTriggerTitle(string $triggerTitle): Trigger
    {
        $this->triggerTitle = $triggerTitle;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): Trigger
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getWeekday(): ?int
    {
        return $this->weekday;
    }

    public function setWeekday(?int $weekday): Trigger
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(?int $day): Trigger
    {
        $this->day = $day;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): Trigger
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): Trigger
    {
        $this->year = $year;

        return $this;
    }

    public function getHour(): ?int
    {
        return $this->hour;
    }

    public function setHour(?int $hour): Trigger
    {
        $this->hour = $hour;

        return $this;
    }

    public function getMinute(): ?int
    {
        return $this->minute;
    }

    public function setMinute(?int $minute): Trigger
    {
        $this->minute = $minute;

        return $this;
    }

    public function getSecond(): ?int
    {
        return $this->second;
    }

    public function setSecond(?int $second): Trigger
    {
        $this->second = $second;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): Trigger
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'className' => $this->getClass(),
            'classNameTitle' => $this->getClassTitle(),
            'trigger' => $this->getTrigger(),
            'triggerTitle' => $this->getTriggerTitle(),
            'parameters' => $this->getParameters(),
            'weekday' => $this->getWeekday(),
            'day' => $this->getDay(),
            'month' => $this->getMonth(),
            'year' => $this->getYear(),
            'hour' => $this->getHour(),
            'minute' => $this->getMinute(),
            'second' => $this->getSecond(),
            'priority' => $this->getPriority(),
        ];
    }
}
