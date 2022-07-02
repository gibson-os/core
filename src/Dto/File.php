<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Dto\File\Error;

class File
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly int $size,
        private readonly int $tmp_name,
        private readonly Error $error,
        private readonly string $full_path,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getTmpName(): int
    {
        return $this->tmp_name;
    }

    public function getError(): Error
    {
        return $this->error;
    }

    public function getFullPath(): string
    {
        return $this->full_path;
    }
}
