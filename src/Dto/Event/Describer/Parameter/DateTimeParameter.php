<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use DateTimeInterface;

class DateTimeParameter extends AbstractParameter
{
    private ?DateTimeInterface $min = null;

    private ?DateTimeInterface $max = null;

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

    protected function getTypeConfig(): array
    {
        return [
            'min' => $this->min === null ? null : $this->min->format('Y-m-d H:i:s'),
            'max' => $this->max === null ? null : $this->max->format('Y-m-d H:i:s'),
        ];
    }
}
