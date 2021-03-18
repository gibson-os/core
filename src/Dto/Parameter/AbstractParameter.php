<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use JsonSerializable;

abstract class AbstractParameter implements JsonSerializable
{
    protected const OPERATOR_EQUAL = '===';

    protected const OPERATOR_NOT_EQUAL = '!==';

    protected const OPERATOR_BIGGER = '>';

    protected const OPERATOR_BIGGER_EQUAL = '>=';

    protected const OPERATOR_SMALLER = '<';

    protected const OPERATOR_SMALLER_EQUAL = '<=';

    private string $title;

    private string $xtype;

    private array $listeners = [];

    private ?string $operator = null;

    private $value;

    abstract protected function getTypeConfig(): array;

    abstract public function getAllowedOperators(): array;

    public function __construct(string $title, string $xtype)
    {
        $this->title = $title;
        $this->xtype = $xtype;
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

    public function setListener(string $field, array $options): void
    {
        $this->listeners[$field] = $options;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): AbstractParameter
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return AbstractParameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'title' => $this->getTitle(),
            'xtype' => $this->getXtype(),
            'allowedOperators' => $this->getAllowedOperators(),
            'config' => $this->getConfig(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
        ];
    }
}
