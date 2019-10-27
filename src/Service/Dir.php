<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;

class Dir extends AbstractService
{
    public function __construct()
    {
    }

    /**
     * @param string $dir
     * @param int    $mode
     *
     * @throws CreateError
     */
    public function create(string $dir, int $mode = 0770): void
    {
        if (!mkdir($dir, $mode, true)) {
            throw new CreateError(sprintf('Ordner %s konnte nicht angelegt werden!', $dir));
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
        if (mb_strlen($dir) == 0) {
            return '';
        }

        if (mb_strrpos($dir, $slash) === mb_strlen($dir) - 1) {
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
        if (mb_strrpos($dir, $slash) == mb_strlen($dir) - 1) {
            return mb_substr($dir, 0, -1);
        }

        return $dir;
    }

    /**
     * @param string $path
     * @param File   $file
     *
     * @return bool
     */
    public function isWritable(string $path, File $file): bool
    {
        $dirs = explode(DIRECTORY_SEPARATOR, $this->removeEndSlash($path));

        while (!file_exists(implode(DIRECTORY_SEPARATOR, $dirs))) {
            array_pop($dirs);
        }

        return $file->isWritable($this->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)), true);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function escapeForGlob(string $path): ?string
    {
        return preg_replace('/(\*|\?|\[)/', '[$1]', $path);
    }
}
