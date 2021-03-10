<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use DateTimeInterface;

class DateTimeParameter extends AbstractParameter
{
    private ?DateTimeInterface $min = null;

    private ?DateTimeInterface $max = null;

    private int $increase = 15;

    public function __construct(string $title)
    {
        parent::__construct($title, 'dateTime');
    }

    public function setRange(?DateTimeInterface $min, DateTimeInterface $max = null): DateTimeParameter
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    public function setIncrease(int $increase): DateTimeParameter
    {
        $this->increase = $increase;

        return $this;
    }

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min === null ? null : $this->min->format('Y-m-d H:i:s'),
            'max' => $this->max === null ? null : $this->max->format('Y-m-d H:i:s'),
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
