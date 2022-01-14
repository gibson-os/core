<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use JsonSerializable;

/**
 * @method Element[] getElements()
 * @method Trigger[] getTriggers()
 */
#[Table]
class Event extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 128)]
    private string $name;

    #[Column]
    private bool $active = true;

    #[Column]
    private bool $async = true;

    #[Column]
    private bool $exitOnError = true;

    #[Column]
    private DateTimeInterface $modified;

    #[Column]
    private ?DateTimeInterface $lastRun = null;

    /**
     * @var Element[]
     */
    #[Constraint('eventId', Element::class)]
    protected array $elements = [];

    /**
     * @var Trigger[]
     */
    #[Constraint('eventId', Trigger::class)]
    protected array $triggers = [];

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

    public function isExitOnError(): bool
    {
        return $this->exitOnError;
    }

    public function setExitOnError(bool $exitOnError): Event
    {
        $this->exitOnError = $exitOnError;

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
     * @param Element[] $elements
     */
    public function setElements(array $elements): Event
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
     * @param Trigger[] $triggers
     */
    public function setTriggers(array $triggers): Event
    {
        $this->triggers = $triggers;

        return $this;
    }

    public function addTrigger(Trigger $trigger): Event
    {
        $this->triggers[] = $trigger;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'active' => $this->isActive(),
            'async' => $this->isAsync(),
            'exitOnError' => $this->isExitOnError(),
            'lastRun' => $this->getLastRun()?->format('Y-m-d H:i:s'),
        ];
    }

    public function getAutoCompleteId(): int
    {
        return $this->getId() ?? 0;
    }
}
