<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Install;

class Success implements InstallDtoInterface
{
    public function __construct(private string $message)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
