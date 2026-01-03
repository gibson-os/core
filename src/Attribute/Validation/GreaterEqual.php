<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use Attribute;
use DateTimeInterface;
use GibsonOS\Core\Validator\GreaterValidator;
use Override;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GreaterEqual extends AbstractValidation
{
    public function __construct(private readonly int|float|DateTimeInterface $greaterThan)
    {
    }

    #[Override]
    public function getAttributeServiceName(): string
    {
        return GreaterValidator::class;
    }

    #[Override]
    public function getMessage(mixed $value): string
    {
        $greaterThan = $this->getGreaterThan();
        $greaterThan = $greaterThan instanceof DateTimeInterface ? $greaterThan->format('Y-m-d H:i:s') : $greaterThan;

        return sprintf('%s must be greater or equal than %s.', $value, $greaterThan);
    }

    public function getGreaterThan(): float|DateTimeInterface|int
    {
        return $this->greaterThan;
    }
}
