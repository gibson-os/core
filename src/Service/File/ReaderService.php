<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Exception\File\ReaderException;
use GibsonOS\Core\Service\File\Reader\ReaderInterface;

class ReaderService
{
    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(
        #[GetServices(['core/src/Service/File/Reader'], ReaderInterface::class)] private readonly array $readers
    ) {
    }

    /**
     * @throws ReaderException
     */
    public function getContent(string $filename): string
    {
        $mimeType = mime_content_type($filename);

        foreach ($this->readers as $reader) {
            if (!in_array($mimeType, $reader->supportedMimeTypes())) {
                continue;
            }

            return $reader->getContent($filename);
        }

        throw new ReaderException(sprintf('Mime type "%s" is not supported!', $mimeType));
    }
}
