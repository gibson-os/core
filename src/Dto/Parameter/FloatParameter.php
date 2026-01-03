<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class FloatParameter extends AbstractParameter
{
    private ?int $min = null;

    private ?int $max = null;

    private ?int $decimals = null;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldNumberField');
    }

    public function setRange(?int $min, ?int $max = null): FloatParameter
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    public function setDecimals(?int $decimals): FloatParameter
    {
        $this->decimals = $decimals;

        return $this;
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
            'decimals' => $this->decimals,
        ];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
            self::OPERATOR_SMALLER,
            self::OPERATOR_SMALLER_EQUAL,
            self::OPERATOR_BIGGER,
            self::OPERATOR_BIGGER_EQUAL,
        ];
    }
}
