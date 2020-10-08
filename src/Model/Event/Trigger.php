<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use mysqlDatabase;

class Trigger extends AbstractModel
{
    public const TRIGGER_CRON = 'cronjob';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $trigger;

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var int|null
     */
    private $weekday;

    /**
     * @var int|null
     */
    private $day;

    /**
     * @var int|null
     */
    private $month;

    /**
     * @var int|null
     */
    private $year;

    /**
     * @var int|null
     */
    private $hour;

    /**
     * @var int|null
     */
    private $minute;

    /**
     * @var int|null
     */
    private $second;

    /**
     * @var int|null
     */
    private $priority;

    /**
     * @var Event
     */
    private $event;

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

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
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
}
