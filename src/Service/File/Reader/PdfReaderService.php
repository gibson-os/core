<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File\Reader;

use Smalot\PdfParser\Parser;

class PdfReaderService implements ReaderInterface
{
    public function __construct(private readonly Parser $parser)
    {
    }

    public function supportedMimeTypes(): array
    {
        return ['application/pdf'];
    }

    /**
     * @throws \Exception
     */
    public function getContent(string $filename): string
    {
        return $this->parser->parseFile($filename)->getText();
    }
}
