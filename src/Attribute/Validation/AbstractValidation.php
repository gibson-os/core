<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute\Validation;

use GibsonOS\Core\Attribute\AttributeInterface;

abstract class AbstractValidation implements AttributeInterface
{
    abstract public function getMessage(mixed $value): string;
}
