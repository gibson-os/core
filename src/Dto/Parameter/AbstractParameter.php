<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use JsonSerializable;
use Override;

abstract class AbstractParameter implements JsonSerializable
{
    protected const OPERATOR_EQUAL = '===';

    protected const OPERATOR_NOT_EQUAL = '!==';

    protected const OPERATOR_BIGGER = '>';

    protected const OPERATOR_BIGGER_EQUAL = '>=';

    protected const OPERATOR_SMALLER = '<';

    protected const OPERATOR_SMALLER_EQUAL = '<=';

    private ?string $image = null;

    private ?string $subText = null;

    private array $listeners = [];

    private ?string $operator = null;

    private mixed $value = null;

    abstract protected function getTypeConfig(): array;

    abstract public function getAllowedOperators(): array;

    public function __construct(private readonly string $title, private readonly string $xtype)
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getXtype(): string
    {
        return $this->xtype;
    }

    public function getConfig(): array
    {
        return array_merge([
            'listeners' => $this->listeners,
        ], $this->getTypeConfig());
    }

    public function setListener(string $field, array $options): self
    {
        $this->listeners[$field] = $options;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(array $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getSubText(): ?string
    {
        return $this->subText;
    }

    public function setSubText(?string $subText): self
    {
        $this->subText = $subText;

        return $this;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->getTitle(),
            'xtype' => $this->getXtype(),
            'allowedOperators' => $this->getAllowedOperators(),
            'config' => $this->getConfig(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
            'subText' => $this->getSubText(),
            'image' => $this->getImage(),
        ];
    }
}
