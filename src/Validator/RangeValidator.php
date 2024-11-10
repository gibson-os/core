<?php
declare(strict_types=1);

namespace GibsonOS\Core\Validator;

use GibsonOS\Core\Attribute\Validation\AbstractValidation;
use GibsonOS\Core\Attribute\Validation\Range;
use GibsonOS\Core\Exception\ValidationException;

class RangeValidator implements ValidatorInterface
{
    /**
     * @throws ValidationException
     */
    public function isValid(AbstractValidation $validation, mixed $value): bool
    {
        if (!$validation instanceof Range) {
            throw new ValidationException(sprintf('Wrong validator %s for %s', $validation::class, $this::class));
        }

        if (!is_numeric($value)) {
            return false;
        }

        if ($validation->getMin() !== min($value, $validation->getMin())) {
            return false;
        }

        if ($validation->getMax() !== max($value, $validation->getMax())) {
            return false;
        }

        return true;
    }
}
