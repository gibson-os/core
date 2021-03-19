<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class OptionParameter extends AbstractParameter
{
    private array $options;

    public function __construct(string $title, array $options)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldComboBox');
        $this->options = $options;
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
