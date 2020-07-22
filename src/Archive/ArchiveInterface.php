<?php
declare(strict_types=1);

namespace GibsonOS\Core\Archive;

interface ArchiveInterface
{
    public function packFiles(string $filename, array $files): void;

    public function unpack(string $filename);
}
