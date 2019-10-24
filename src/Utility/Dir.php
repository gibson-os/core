<?php
namespace GibsonOS\Core\Utility;

use GibsonOS\Core\Exception\CreateError;

class Dir
{
    /**
     * @param string $dir
     * @param int $mode
     * @throws CreateError
     */
    static function create($dir, $mode = 0770)
    {
        if (!mkdir($dir, $mode, true)) {
            throw new CreateError('Ordner ' . $dir . ' konnte nicht angelegt werden!');
        }
    }

    /**
     * @param string $dir
     * @param string $slash
     * @return string
     */
    static function setEndSlash($dir, $slash = DIRECTORY_SEPARATOR)
    {
        if (mb_strlen($dir) == 0) {
            return '';
        }

        if (mb_strrpos($dir, $slash) == mb_strlen($dir)-1) {
            return $dir;
        }

        return $dir . $slash;
    }

    /**
     * @param string $dir
     * @param string $slash
     * @return string
     */
    static function removeEndSlash($dir, $slash = DIRECTORY_SEPARATOR)
    {
        if (mb_strrpos($dir, $slash) == mb_strlen($dir)-1) {
            return mb_substr($dir, 0, -1);
        }

        return $dir;
    }

    /**
     * @param string $path
     * @return bool
     */
    static function isWritable($path)
    {
        $dirs = explode(DIRECTORY_SEPARATOR, self::removeEndSlash($path));

        while (!file_exists(implode(DIRECTORY_SEPARATOR, $dirs))) {
            array_pop($dirs);
        }

        return File::isWritable(self::setEndSlash(implode(DIRECTORY_SEPARATOR, $dirs)), true);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function escapeForGlob($path)
    {
        return preg_replace('/(\*|\?|\[)/', '[$1]', $path);
    }
}