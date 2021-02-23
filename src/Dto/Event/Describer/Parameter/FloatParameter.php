<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

class FloatParameter extends AbstractParameter
{
    private ?int $min = null;

    private ?int $max = null;

    private ?int $decimals = null;

    public function __construct(string $title)
    {
        parent::__construct($title, 'int');
    }

    public function setRange(?int $min, int $max = null): FloatParameter
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

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
            'decimals' => $this->decimals,
        ];
    }
}
