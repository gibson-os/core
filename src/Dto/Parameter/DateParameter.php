<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use DateTimeInterface;

class DateParameter extends AbstractParameter
{
    private ?DateTimeInterface $min = null;

    private ?DateTimeInterface $max = null;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldDate');
    }

    public function setRange(?DateTimeInterface $min, DateTimeInterface $max = null): DateParameter
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min?->format('Y-m-d'),
            'max' => $this->max?->format('Y-m-d'),
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
