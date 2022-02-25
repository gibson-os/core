<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Dto\Event\Command;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use JsonException;
use JsonSerializable;
use Serializable;

/**
 * @method Event        getEvent()
 * @method Element      setEvent(Event $event)
 * @method Element|null getParent()
 * @method Element      setParent(?Element $element)
 * @method Element[]    getChildren()
 * @method Element      addChildren(Element[] $elements)
 * @method Element      setChildren(Element[] $elements)
 */
#[Table]
#[Key(unique: true, columns: ['parent_id', 'order'])]
class Element extends AbstractModel implements Serializable, JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $eventId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $parentId = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $order = 0;

    /**
     * @var class-string
     */
    #[Column(length: 512)]
    private string $class;

    #[Column(length: 255)]
    private string $method;

    /**
     * Required for store.
     */
    private ?string $classTitle = null;

    /**
     * Required for store.
     */
    private ?string $methodTitle = null;

    #[Column]
    private array $parameters = [];

    #[Column]
    private ?Command $command = null;

    #[Column]
    private array $returns = [];

    #[Constraint]
    protected Event $event;

    #[Constraint]
    protected ?Element $parent = null;

    /**
     * @var Element[]|null
     */
    #[Constraint('parent', Element::class)]
    protected array $children = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Element
    {
        $this->id = $id;

        return $this;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): Element
    {
        $this->eventId = $eventId;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): Element
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): Element
    {
        $this->order = $order;

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
    public function setClass(string $class): Element
    {
        $this->class = $class;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): Element
    {
        $this->method = $method;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): Element
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getCommand(): ?Command
    {
        return $this->command;
    }

    public function setCommand(?Command $command): Element
    {
        $this->command = $command;

        return $this;
    }

    public function getReturns(): array
    {
        return $this->returns;
    }

    public function setReturns(array $returns): Element
    {
        $this->returns = $returns;

        return $this;
    }

    public function getClassTitle(): ?string
    {
        return $this->classTitle;
    }

    public function setClassTitle(?string $classTitle): Element
    {
        $this->classTitle = $classTitle;

        return $this;
    }

    public function getMethodTitle(): ?string
    {
        return $this->methodTitle;
    }

    public function setMethodTitle(?string $methodTitle): Element
    {
        $this->methodTitle = $methodTitle;

        return $this;
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->getId(),
            'order' => $this->getOrder(),
            'className' => $this->getClass(),
            'classNameTitle' => $this->getClassTitle(),
            'method' => $this->getMethod(),
            'methodTitle' => $this->getMethodTitle(),
            'command' => $this->getCommand(),
            'returns' => $this->getReturns(),
            'parameters' => $this->getParameters(),
            'leaf' => true,
        ];

        if (!empty($this->getChildren())) {
            $data['data'] = $this->getChildren();
            $data['leaf'] = false;
        }

        return $data;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->getId(),
            'eventId' => $this->getEventId(),
            'parentId' => $this->getParentId(),
            'order' => $this->getOrder(),
            'command' => $this->getCommand(),
            'class' => $this->getClass(),
            'method' => $this->getMethod(),
            'params' => $this->getParameters(),
            'value' => $this->getReturns(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this
            ->setId($data['id'])
            ->setEventId($data['eventId'])
            ->setParentId($data['parentId'])
            ->setOrder($data['order'])
            ->setCommand($data['command'])
            ->setClass($data['class'])
            ->setMethod($data['method'])
            ->setParameters($data['params'])
            ->setReturns($data['value'])
        ;
    }
}
