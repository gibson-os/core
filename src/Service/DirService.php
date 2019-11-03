<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;

class DirService extends AbstractService
{
    /**
     * @param string $dir
     * @param int    $mode
     *
     * @throws CreateError
     */
    public function create(string $dir, int $mode = 0770): void
    {
        if (
            file_exists($dir) ||
            !mkdir($dir, $mode, true)
        ) {
            throw new CreateError(sprintf('Ordner "%s" konnte nicht angelegt werden!', $dir));
        }
    }

    /**
     * @param string $dir
     * @param string $slash
     *
     * @return string
     */
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

    /**
     * @param string $dir
     * @param string $slash
     *
     * @return string
     */
    public function removeEndSlash(string $dir, string $slash = DIRECTORY_SEPARATOR): string
    {
        $slashLength = mb_strlen($slash);
        $lastSlashPosition = mb_strrpos($dir, $slash);

        if (
            $lastSlashPosition !== 0 &&
            $lastSlashPosition === mb_strlen($dir) - $slashLength
        ) {
            return mb_substr($dir, 0, 0 - $slashLength);
        }

        return $dir;
    }

    /**
     * @param string      $path
     * @param FileService $file
     *
     * @return bool
     */
    public function isWritable(string $path, FileService $file): bool
    {
        $dirs = explode(DIRECTORY_SEPARATOR, $this->removeEndSlash($path));

        while (!$file->exists($this->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)))) {
            array_pop($dirs);
        }

        return $file->isWritable($this->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)), true);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function escapeForGlob(string $path): string
    {
        return (string) preg_replace('/(\*|\?|\[)/', '[$1]', $path);
    }
}
