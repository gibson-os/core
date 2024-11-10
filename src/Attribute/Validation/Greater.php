<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use Attribute;
use DateTimeInterface;
use GibsonOS\Core\Validator\GreaterAbstractValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Greater extends AbstractValidation
{
    public function __construct(private readonly int|float|DateTimeInterface $greaterThan)
    {
    }

    public function getAttributeServiceName(): string
    {
        return GreaterAbstractValidator::class;
    }

    public function getMessage(mixed $value): string
    {
        $greaterThan = $this->getGreaterThan();
        $greaterThan = $greaterThan instanceof DateTimeInterface ? $greaterThan->format('Y-m-d H:i:s') : $greaterThan;

        return sprintf('%s must be greater than %s.', $value, $greaterThan);
    }

    public function getGreaterThan(): float|DateTimeInterface|int
    {
        return $this->greaterThan;
    }
}
