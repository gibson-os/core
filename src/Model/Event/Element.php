<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use JsonSerializable;
use mysqlDatabase;
use Serializable;

#[Table]
class Element extends AbstractModel implements Serializable, JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column]
    private int $eventId;

    #[Column]
    private ?int $parentId = null;

    #[Column]
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

    #[Column(type: Column::TYPE_JSON)]
    private ?string $parameters = null;

    #[Column(type: Column::TYPE_ENUM, values: ['if', 'else', 'else_if', 'while', 'do_while'])]
    private ?string $command = null;

    #[Column(type: Column::TYPE_JSON)]
    private ?string $returns = null;

    private Event $event;

    private ?Element $parent = null;

    /**
     * @var Element[]|null
     */
    private ?array $children = null;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->event = new Event();
    }

    public static function getTableName(): string
    {
        return 'event_element';
    }

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

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(?string $parameters): Element
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): Element
    {
        $this->command = $command;

        return $this;
    }

    public function getReturns(): ?string
    {
        return $this->returns;
    }

    public function setReturns(?string $returns): Element
    {
        $this->returns = $returns;

        return $this;
    }

    public function getEvent(): Event
    {
        $this->loadForeignRecord($this->event, $this->getEventId());

        return $this->event;
    }

    public function setEvent(Event $event): Element
    {
        $this->event = $event;
        $this->setEventId((int) $event->getId());

        return $this;
    }

    public function getParent(): ?Element
    {
        $parentId = $this->getParentId();

        if ($parentId != null) {
            if ($this->parent === null) {
                $this->parent = new Element();
            }

            $this->loadForeignRecord($this->parent, $parentId);
        }

        return $this->parent;
    }

    public function setParent(?Element $parent): Element
    {
        $this->parent = $parent;
        $this->setParentId($parent instanceof Element ? (int) $parent->getId() : null);

        return $this;
    }

    /**
     * @return Element[]|null
     */
    public function getChildren(): ?array
    {
        if ($this->children === null) {
            $this->loadChildren();
        }

        return $this->children;
    }

    public function loadChildren(): void
    {
        /** @var Element[] $children */
        $children = $this->loadForeignRecords(
            Element::class,
            $this->getId(),
            Element::getTableName(),
            'parent_id'
        );

        $this->setChildren($children);
    }

    /**
     * @param Element[]|null $children
     */
    public function setChildren(?array $children): Element
    {
        $this->children = $children;

        return $this;
    }

    public function addChildren(Element $children): Element
    {
        $this->children[] = $children;

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
        return serialize([
            'id' => $this->getId(),
            'eventId' => $this->getEventId(),
            'parentId' => $this->getParentId(),
            'order' => $this->getOrder(),
            'command' => $this->getCommand(),
            'class' => $this->getClass(),
            'method' => $this->getMethod(),
            'params' => $this->getParameters(),
            'value' => $this->getReturns(),
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $unserialized = unserialize($data);

        $this
            ->setId($unserialized['id'])
            ->setEventId($unserialized['eventId'])
            ->setParentId($unserialized['parentId'])
            ->setOrder($unserialized['order'])
            ->setCommand($unserialized['command'])
            ->setClass($unserialized['class'])
            ->setMethod($unserialized['method'])
            ->setParameters($unserialized['params'])
            ->setReturns($unserialized['value'])
        ;
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
            'returns' => JsonUtility::decode($this->getReturns() ?? 'null'),
            'parameters' => JsonUtility::decode($this->getParameters() ?? 'null'),
            'leaf' => true,
        ];

        if (!empty($this->getChildren())) {
            $data['data'] = $this->getChildren();
            $data['leaf'] = false;
        }

        return $data;
    }
}
