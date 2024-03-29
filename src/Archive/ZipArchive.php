<?php
declare(strict_types=1);

namespace GibsonOS\Core\Archive;

use GibsonOS\Core\Exception\ArchiveException;
use ZipArchive as StandardZipArchive;

class ZipArchive implements ArchiveInterface
{
    public function __construct(private readonly StandardZipArchive $zipArchive)
    {
    }

    /**
     * @param string[] $files
     *
     * @throws ArchiveException
     */
    public function packFiles(string $filename, array $files): void
    {
        if ($this->zipArchive->open($filename, StandardZipArchive::CREATE) !== true) {
            throw new ArchiveException(sprintf('Cant create zip archive %s', $filename));
        }

        foreach ($files as $file) {
            if (!$this->zipArchive->addFile($file)) {
                throw new ArchiveException(sprintf('Cant add file %s to zip archive %s', $file, $filename));
            }
        }

        if (!$this->zipArchive->close()) {
            throw new ArchiveException(sprintf('Cant close zip archive %s', $filename));
        }
    }

    public function unpack(string $filename)
    {
        // TODO: Implement unpack() method.
    }
}
