<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use JsonSerializable;

class Event extends AbstractModel implements JsonSerializable
{
    private ?int $id = null;

    private string $name;

    private bool $active;

    private bool $async;

    private DateTimeInterface $modified;

    private ?DateTimeInterface $lastRun = null;

    /**
     * @var Element[]|null
     */
    private ?array $elements = null;

    /**
     * @var Trigger[]|null
     */
    private ?array $triggers = null;

    public static function getTableName(): string
    {
        return 'event';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Event
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Event
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Event
    {
        $this->active = $active;

        return $this;
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function setAsync(bool $async): Event
    {
        $this->async = $async;

        return $this;
    }

    public function getModified(): DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(DateTimeInterface $modified): Event
    {
        $this->modified = $modified;

        return $this;
    }

    public function getLastRun(): ?DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?DateTimeInterface $lastRun): Event
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    /**
     * @throws DateTimeError
     *
     * @return Element[]|null
     */
    public function getElements(): ?array
    {
        if ($this->elements === null) {
            $this->loadElements();
        }

        return $this->elements;
    }

    /**
     * @param Element[]|null $elements
     */
    public function setElements(?array $elements): Event
    {
        $this->elements = $elements;

        return $this;
    }

    public function addElement(Element $element): Event
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function loadElements()
    {
        /** @var Element[] $elements */
        $elements = $this->loadForeignRecords(
            Element::class,
            $this->getId(),
            Element::getTableName(),
            'event_id'
        );

        $this->setElements($elements);
    }

    /**
     * @throws DateTimeError
     *
     * @return Trigger[]|null
     */
    public function getTriggers(): ?array
    {
        if ($this->triggers === null) {
            $this->loadTriggers();
        }

        return $this->triggers;
    }

    /**
     * @param Trigger[]|null $triggers
     */
    public function setTriggers(?array $triggers): Event
    {
        $this->triggers = $triggers;

        return $this;
    }

    public function addTrigger(Trigger $trigger): Event
    {
        $this->triggers[] = $trigger;

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function loadTriggers()
    {
        /** @var Trigger[] $triggers */
        $triggers = $this->loadForeignRecords(
            Trigger::class,
            $this->getId(),
            Trigger::getTableName(),
            'event_id'
        );

        $this->setTriggers($triggers);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'active' => $this->isActive(),
            'async' => $this->isAsync(),
        ];
    }
}
