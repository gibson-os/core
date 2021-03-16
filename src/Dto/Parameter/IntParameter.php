<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class IntParameter extends AbstractParameter
{
    private ?int $min = null;

    private ?int $max = null;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosFormNumberfield');
    }

    public function setRange(?int $min, int $max = null): IntParameter
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
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
