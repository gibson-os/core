<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\GetError;

class DirService
{
    /**
     * @throws CreateError
     */
    public function create(string $dir, int $mode = 0770): void
    {
        if (
            file_exists($dir)
            || !mkdir($dir, $mode, true)
        ) {
            throw new CreateError(sprintf('Ordner "%s" konnte nicht angelegt werden!', $dir));
        }
    }

    public function addEndSlash(string $dir, string $slash = DIRECTORY_SEPARATOR): string
    {
        if (mb_strlen($dir) === 0) {
            return $slash;
        }

        if (mb_strrpos($dir, $slash) === mb_strlen($dir) - mb_strlen($slash)) {
            return $dir;
        }

        return $dir . $slash;
    }

    public function removeEndSlash(string $dir, string $slash = DIRECTORY_SEPARATOR): string
    {
        $slashLength = mb_strlen($slash);
        $lastSlashPosition = mb_strrpos($dir, $slash);

        if (
            $lastSlashPosition !== 0
            && $lastSlashPosition === mb_strlen($dir) - $slashLength
        ) {
            return mb_substr($dir, 0, 0 - $slashLength);
        }

        return $dir;
    }

    public function isWritable(string $path, FileService $file): bool
    {
        $dirs = explode(DIRECTORY_SEPARATOR, $this->removeEndSlash($path));

        while (!$file->exists($this->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)))) {
            array_pop($dirs);
        }

        return $file->isWritable($this->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)), true);
    }

    public function escapeForGlob(string $path): string
    {
        return (string) preg_replace('/(\*|\?|\[)/', '[$1]', $path);
    }

    /**
     * @throws GetError
     *
     * @return string[]
     */
    public function getFiles(string $path, string $pattern = '*'): array
    {
        $files = glob($this->escapeForGlob($this->addEndSlash($path)) . $pattern);

        if (!is_array($files)) {
            throw new GetError(sprintf('Verzeichnis "%s" kann nicht gelesen werden!', $path));
        }

        return $files;
    }

    public function getDirName(string $path, $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        $pathParts = explode($directorySeparator, $this->removeEndSlash($path, $directorySeparator));
        unset($pathParts[count($pathParts) - 1]);

        return $this->addEndSlash(implode($directorySeparator, $pathParts), $directorySeparator);
    }
}
