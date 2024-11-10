<?php
declare(strict_types=1);

namespace GibsonOS\Core\Validator;

use GibsonOS\Core\Attribute\Validation\AbstractValidation;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;

abstract class AbstractValidator implements AttributeServiceInterface
{
    abstract public function isValid(AbstractValidation $validation, mixed $value): bool;
}
