<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use DateTimeInterface;

class TimeParameter extends AbstractParameter
{
    private ?DateTimeInterface $min = null;

    private ?DateTimeInterface $max = null;

    private int $increase = 15;

    public function __construct(string $title)
    {
        parent::__construct($title, 'date');
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
}