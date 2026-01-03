<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File\Reader;

use Override;

class TextReaderService implements ReaderInterface
{
    #[Override]
    public function supportedMimeTypes(): array
    {
        return ['text/plain'];
    }

    #[Override]
    public function getContent(string $filename): string
    {
        return file_get_contents($filename) ?: '';
    }
}
