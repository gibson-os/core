<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Event;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Utility\JsonUtility;
use JsonSerializable;
use mysqlDatabase;
use Serializable;

class Element extends AbstractModel implements Serializable, JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var int
     */
    private $order = 0;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $command;

    /**
     * @var string|null
     */
    private $operator;

    /**
     * @var string|null
     */
    private $returns;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Element|null
     */
    private $parent;

    /**
     * @var Element[]
     */
    private $children;

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

    public function getClass(): string
    {
        return $this->class;
    }

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

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): Element
    {
        $this->operator = $operator;

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

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
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

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getParent(): ?Element
    {
        if ($this->getParentId() != null) {
            if ($this->parent === null) {
                $this->parent = new Element();
            }

            $this->loadForeignRecord($this->parent, $this->getParentId());
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
     * @throws DateTimeError
     *
     * @return Element[]
     */
    public function getChildren(): array
    {
        if ($this->children === null) {
            $this->loadChildren();
        }

        return $this->children;
    }

    /**
     * @throws DateTimeError
     */
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
     * @param Element[] $children
     */
    public function setChildren(array $children): Element
    {
        $this->children = $children;

        return $this;
    }

    public function addChildren(Element $children): Element
    {
        $this->children[] = $children;

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
            'operator' => $this->getOperator(),
            'params' => $this->getParameters(),
            'value' => $this->getReturns(),
        ]);
    }

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this
            ->setId($unserialized['id'])
            ->setEventId($unserialized['eventId'])
            ->setParentId($unserialized['parentId'])
            ->setOrder($unserialized['order'])
            ->setCommand($unserialized['command'])
            ->setClass($unserialized['class'])
            ->setMethod($unserialized['method'])
            ->setOperator($unserialized['operator'])
            ->setParameters($unserialized['params'])
            ->setReturns($unserialized['value'])
        ;
    }

    public function jsonSerialize()
    {
        $data = [
            'id' => $this->getId(),
            'order' => $this->getOrder(),
            'className' => $this->getClass(),
            'method' => $this->getMethod(),
            'command' => $this->getCommand(),
            'operator' => $this->getOperator(),
            'returns' => JsonUtility::decode($this->getReturns() ?? 'null'),
            'parameters' => JsonUtility::decode($this->getParameters() ?? 'null'),
            'leaf' => true,
        ];

        if (count($this->getChildren())) {
            $data['data'] = $this->getChildren();
            $data['leaf'] = false;
        }

        return $data;
    }
}
