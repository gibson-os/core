<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class BoolParameter extends AbstractParameter
{
    private string $inputValue = 'true';

    private string $uncheckedValue = 'false';

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldCheckbox');
    }

    public function setInputValue(string $inputValue): BoolParameter
    {
        $this->inputValue = $inputValue;

        return $this;
    }

    public function setUncheckedValue(string $uncheckedValue): BoolParameter
    {
        $this->uncheckedValue = $uncheckedValue;

        return $this;
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'inputValue' => $this->inputValue,
            'uncheckedValue' => $this->uncheckedValue,
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
}
