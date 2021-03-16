<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

abstract class AbstractParameter
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
}
