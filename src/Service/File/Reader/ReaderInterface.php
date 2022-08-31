<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File\Reader;

interface ReaderInterface
{
    public function supportedMimeTypes(): array;

    public function getContent(string $filename): string;
}
