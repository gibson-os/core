<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use Attribute;
use DateTimeInterface;
use GibsonOS\Core\Validator\RangeValidator;
use Override;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Range extends AbstractValidation
{
    public function __construct(
        private readonly int|float|DateTimeInterface $min,
        private readonly int|float|DateTimeInterface $max,
    ) {
    }

    #[Override]
    public function getAttributeServiceName(): string
    {
        return RangeValidator::class;
    }

    #[Override]
    public function getMessage(mixed $value): string
    {
        $min = $this->getMin();
        $max = $this->getMax();
        $min = $min instanceof DateTimeInterface ? $min->format('Y-m-d H:i:s') : $min;
        $max = $max instanceof DateTimeInterface ? $max->format('Y-m-d H:i:s') : $max;

        return sprintf(
            '%s is out of range (%s - %s).',
            $value,
            $min,
            $max,
        );
    }

    public function getMin(): float|DateTimeInterface|int
    {
        return $this->min;
    }

    public function getMax(): float|DateTimeInterface|int
    {
        return $this->max;
    }
}
