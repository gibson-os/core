<?php
declare(strict_types=1);

namespace GibsonOS\Core\Validator;

use GibsonOS\Core\Attribute\Validation\AbstractValidation;

interface ValidatorInterface
{
    public function isValid(AbstractValidation $validation, mixed $value): bool;
}
