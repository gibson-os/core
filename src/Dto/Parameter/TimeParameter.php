<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use DateTimeInterface;

class TimeParameter extends AbstractParameter
{
    private ?DateTimeInterface $min = null;

    private ?DateTimeInterface $max = null;

    private int $increase = 15;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldTime');
    }

    public function setRange(?DateTimeInterface $min, DateTimeInterface $max = null): TimeParameter
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    public function setIncrease(int $increase): TimeParameter
    {
        $this->increase = $increase;

        return $this;
    }

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min === null ? null : $this->min->format('Y-m-d'),
            'max' => $this->max === null ? null : $this->max->format('Y-m-d'),
            'increase' => $this->increase,
        ];
    }

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
