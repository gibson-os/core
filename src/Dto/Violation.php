<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Validator\ValidatorInterface;

class Violation
{
    public function __construct(
        private readonly string $message,
        private readonly ValidatorInterface $validator,
        private readonly ?string $objectName = null,
        private readonly ?string $propertyName = null,
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getObjectName(): ?string
    {
        return $this->objectName;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }
}
