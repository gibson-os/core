<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use DateTimeInterface;
use GibsonOS\Core\Validator\LowerValidator;

class LowerEqual extends AbstractValidation
{
    public function __construct(private readonly int|float|DateTimeInterface $lowerThan)
    {
    }

    public function getAttributeServiceName(): string
    {
        return LowerValidator::class;
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
