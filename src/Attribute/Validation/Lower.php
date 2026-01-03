<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use Attribute;
use DateTimeInterface;
use GibsonOS\Core\Validator\LowerValidator;
use Override;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Lower extends AbstractValidation
{
    public function __construct(private readonly int|float|DateTimeInterface $lowerThan)
    {
    }

    #[Override]
    public function getAttributeServiceName(): string
    {
        return LowerValidator::class;
    }

    #[Override]
    public function getMessage(mixed $value): string
    {
        $lowerThan = $this->getLowerThan();
        $lowerThan = $lowerThan instanceof DateTimeInterface ? $lowerThan->format('Y-m-d H:i:s') : $lowerThan;

        return sprintf('%s must be lower than %s.', $value, $lowerThan);
    }

    public function getLowerThan(): float|DateTimeInterface|int
    {
        return $this->lowerThan;
    }
}
