<?php
declare(strict_types=1);

namespace GibsonOS\Core\Validator;

use DateTimeInterface;
use GibsonOS\Core\Attribute\Validation\AbstractValidation;
use GibsonOS\Core\Attribute\Validation\Lower;
use GibsonOS\Core\Attribute\Validation\LowerEqual;
use GibsonOS\Core\Exception\ValidationException;
use Override;

class LowerValidator extends AbstractValidator
{
    /**
     * @throws ValidationException
     */
    #[Override]
    public function isValid(AbstractValidation $validation, mixed $value): bool
    {
        if (!$validation instanceof Lower && !$validation instanceof LowerEqual) {
            throw new ValidationException(sprintf('Wrong validator %s for %s', $validation::class, $this::class));
        }

        if (!is_numeric($value) && !$value instanceof DateTimeInterface) {
            return false;
        }

        $lowerThan = $validation->getLowerThan();

        return match ($validation::class) {
            Lower::class => $value > $lowerThan,
            LowerEqual::class => $value >= $lowerThan,
        };
    }
}
