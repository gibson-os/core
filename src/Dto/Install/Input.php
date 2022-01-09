<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Install;

class Input implements InstallDtoInterface
{
    public function __construct(private string $key, private string $message, private ?string $value = null)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): Input
    {
        $this->value = $value;

        return $this;
    }
}
