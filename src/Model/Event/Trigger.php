<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use JsonSerializable;
use mysqlDatabase;

class Trigger extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private int $eventId;

    private string $class;

    private string $trigger;

    /**
     * Required for store.
     */
    private string $classTitle;

    /**
     * Required for store.
     */
    private string $triggerTitle;

    private ?string $parameters = null;

    private ?int $weekday = null;

    private ?int $day = null;

    private ?int $month = null;

    private ?int $year = null;

    private ?int $hour = null;

    private ?int $minute = null;

    private ?int $second = null;

    private ?int $priority = null;

    private Event $event;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->event = new Event();
    }

    public static function getTableName(): string
    {
        return 'event_trigger';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Trigger
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

    public function getClass(): string
    {
        return $this->class;
    }

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

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(?string $parameters): Trigger
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

    public function getEvent(): Event
    {
        $this->loadForeignRecord($this->event, $this->getEventId());

        return $this->event;
    }

    public function setEvent(Event $event): Trigger
    {
        $this->event = $event;
        $this->setEventId((int) $event->getId());

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'className' => $this->getClass(),
            'classNameTitle' => $this->getClassTitle(),
            'trigger' => $this->getTrigger(),
            'triggerTitle' => $this->getTriggerTitle(),
            'parameters' => JsonUtility::decode($this->getParameters() ?? 'null'),
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
