<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class OptionParameter extends AbstractParameter
{
    private bool $multiple = false;

    public function __construct(string $title, private array $options)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldComboBox');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'options' => $this->options,
            'multiple' => $this->multiple,
        ];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }

    public function setMultiple(bool $multiple): OptionParameter
    {
        $this->multiple = $multiple;

        return $this;
    }
}
