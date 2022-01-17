<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Model\Event\Trigger;
use JsonSerializable;
use mysqlDatabase;

/**
 * @method Element[] getElements()
 * @method Event     addElements(Element[] $elements)
 * @method Event     setElements(Element[] $elements)
 * @method Trigger[] getTriggers()
 * @method Event     addTriggers(Trigger[] $triggers)
 * @method Event     setTriggers(Trigger[] $triggers)
 */
#[Table]
class Event extends AbstractModel implements JsonSerializable, AutoCompleteModelInterface
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 128)]
    #[Key(true)]
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
    #[Constraint('event', Element::class)]
    protected array $elements = [];

    /**
     * @var Trigger[]
     */
    #[Constraint('event', Trigger::class)]
    protected array $triggers = [];

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->modified = new DateTimeImmutable();
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
