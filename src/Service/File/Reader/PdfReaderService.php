<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\File\Reader;

use Exception;
use Override;
use Smalot\PdfParser\Parser;

class PdfReaderService implements ReaderInterface
{
    public function __construct(private readonly Parser $parser)
    {
    }

    #[Override]
    public function supportedMimeTypes(): array
    {
        return ['application/pdf'];
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function getContent(string $filename): string
    {
        return $this->parser->parseFile($filename)->getText();
    }
}
