<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class OptionParameter extends AbstractParameter
{
    public function __construct(string $title, private array $options)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldComboBox');
    }

    protected function getTypeConfig(): array
    {
        return [
            'options' => $this->options,
        ];
    }

    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }
}
