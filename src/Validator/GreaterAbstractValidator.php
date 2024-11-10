<?php
declare(strict_types=1);

namespace GibsonOS\Core\Validator;

use DateTimeInterface;
use GibsonOS\Core\Attribute\Validation\AbstractValidation;
use GibsonOS\Core\Attribute\Validation\Greater;
use GibsonOS\Core\Attribute\Validation\GreaterEqual;
use GibsonOS\Core\Exception\ValidationException;

class GreaterAbstractValidator extends AbstractValidator
{
    /**
     * @throws ValidationException
     */
    public function isValid(AbstractValidation $validation, mixed $value): bool
    {
        if (!$validation instanceof Greater && !$validation instanceof GreaterEqual) {
            throw new ValidationException(sprintf('Wrong validator %s for %s', $validation::class, $this::class));
        }

        if (!is_numeric($value) && !$value instanceof DateTimeInterface) {
            return false;
        }

        $greaterThan = $validation->getGreaterThan();

        return match ($validation::class) {
            Greater::class => $value > $greaterThan,
            GreaterEqual::class => $value >= $greaterThan,
        };
    }
}
