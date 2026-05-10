<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class SliderParameter extends AbstractParameter
{
    private int $minValue = 0;

    private int $maxValue = 100;

    private int $increment = 1;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldSliderField');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'minValue' => $this->minValue,
            'maxValue' => $this->maxValue,
            'increment' => $this->increment,
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

    public function setMinValue(int $minValue): SliderParameter
    {
        $this->minValue = $minValue;

        return $this;
    }

    public function setMaxValue(int $maxValue): SliderParameter
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    public function setIncrement(int $increment): SliderParameter
    {
        $this->increment = $increment;

        return $this;
    }
}
