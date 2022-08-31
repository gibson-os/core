<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File\Reader;

class TextReaderService implements ReaderInterface
{
    public function supportedMimeTypes(): array
    {
        return ['text/plain'];
    }

    public function getContent(string $filename): string
    {
        return file_get_contents($filename);
    }
}
