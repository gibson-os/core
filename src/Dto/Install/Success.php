<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Install;

use Override;

class Success implements InstallDtoInterface
{
    public function __construct(private string $message)
    {
    }

    #[Override]
    public function getMessage(): string
    {
        return $this->message;
    }
}
