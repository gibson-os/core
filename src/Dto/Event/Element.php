<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event;

use JsonSerializable;

class Element implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $left;

    /**
     * @var int
     */
    private $right;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $command;

    /**
     * @var string|null
     */
    private $operator;

    /**
     * @var array
     */
    private $returns = [];

    /**
     * @var Element[]
     */
    private $children = [];

    public function __construct(int $id, string $class, string $method, int $left, int $right)
    {
        $this->id = $id;
        $this->class = $class;
        $this->method = $method;
        $this->left = $left;
        $this->right = $right;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Element
    {
        $this->id = $id;

        return $this;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): Element
    {
        $this->left = $left;

        return $this;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function setRight(int $right): Element
    {
        $this->right = $right;

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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): Element
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

    public function getReturns(): array
    {
        return $this->returns;
    }

    public function setReturns(array $returns): Element
    {
        $this->returns = $returns;

        return $this;
    }

    /**
     * @return Element[]
     */
    public function getChildren(): array
    {
        return $this->children;
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'left' => $this->getLeft(),
            'right' => $this->getRight(),
            'class' => $this->getClass(),
            'method' => $this->getMethod(),
            'operator' => $this->getOperator(),
            'value' => $this->getReturns(),
            'params' => $this->getParameters(),
            'data' => $this->getChildren(),
        ];
    }
}
