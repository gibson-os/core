<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use Attribute;
use DateTimeInterface;
use GibsonOS\Core\Validator\LowerAbstractValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LowerEqual extends AbstractValidation
{
    public function __construct(private readonly int|float|DateTimeInterface $lowerThan)
    {
    }

    public function getAttributeServiceName(): string
    {
        return LowerAbstractValidator::class;
    }

    public function getMessage(mixed $value): string
    {
        $lowerThan = $this->getLowerThan();
        $lowerThan = $lowerThan instanceof DateTimeInterface ? $lowerThan->format('Y-m-d H:i:s') : $lowerThan;

        return sprintf('%s must be lower or equal than %s.', $value, $lowerThan);
    }

    public function getLowerThan(): float|DateTimeInterface|int
    {
        return $this->lowerThan;
    }
}