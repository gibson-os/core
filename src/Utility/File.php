<?php
namespace GibsonOS\Core\Utility;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileExistsError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

class File
{
    /**
     * @param string $from
     * @param string $to
     * @throws CreateError
     * @throws GetError
     * @throws SetError
     */
    static function copy(string $from, string $to)
    {
        if (is_dir($from)) {
            $from = Dir::setEndSlash($from);
            $to = Dir::setEndSlash($to);

            if (!file_exists($to)) {
                Dir::create($to);
            }

            $chmod = self::getPerms($from);
            self::setPerms($to, $chmod);

            $owner = self::getOwner($from);
            self::setOwner($to, $owner);

            $group = self::getGroup($from);
            self::setGroup($to, $group);

            foreach (glob(Dir::escapeForGlob($from) . '*') as $path) {
                $filename = self::getFilename($path);
                self::copy($path, $to . $filename);
            }
        } else if (!copy($from, $to)) {
            throw new CreateError('Konnte ' . $from . ' nicht nach ' . $to . ' kopieren!');
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @param bool $ignore
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SetError
     */
    static function move(string $from, string $to, bool $overwrite = false, bool $ignore = false)
    {
        if (is_dir($from)) {
            $from = Dir::setEndSlash($from);
            $to = Dir::setEndSlash($to);

            if (!file_exists($to)) {
                Dir::create($to);
            }

            $chmod = self::getPerms($from);
            self::setPerms($to, $chmod);

            $owner = self::getOwner($from);
            self::setOwner($to, $owner);

            $group = self::getGroup($from);
            self::setGroup($to, $group);

            foreach (glob(Dir::escapeForGlob($from) . '*') as $path) {
                $filename = self::getFilename($path);
                self::move($path, $to . $filename, $overwrite, $ignore);
            }

            self::delete($from, null);
        } else if (self::isWritable($to, $overwrite)) {
            if (!rename($from, $to)) {
                throw new CreateError('Konnte ' . $from . ' nicht nach ' . $to . ' verschieben!');
            }
        }
    }

    /**
     * @param string $path
     * @return int
     * @throws GetError
     */
    static function getPerms(string $path): int
    {
        $chmod = fileperms($path);

        if ($chmod === false) {
            throw new GetError('Konnte Dateiberechtigungen von ' . $path . ' nicht ermitteln!');
        }

        return $chmod;
    }

    /**
     * @param string $path
     * @param int $chmod
     * @throws SetError
     */
    static function setPerms(string $path, int $chmod)
    {
        if (!chmod($path, $chmod)) {
            throw new SetError('Konnte Dateiberechtigungen von ' . $path . ' nicht setzen!');
        }
    }

    /**
     * @param string $path
     * @return int
     * @throws GetError
     */
    static function getOwner(string $path): int
    {
        $owner = fileowner($path);

        if ($owner === false) {
            throw new GetError('Konnte Eigentümer von ' . $path . ' nicht ermitteln!');
        }

        return $owner;
    }

    /**
     * @param string $path
     * @param int $owner
     * @throws SetError
     */
    static function setOwner(string $path, int $owner)
    {
        if (!chown($path, $owner)) {
            throw new SetError('Konnte Eigentümer von ' . $path . ' nicht setzen!');
        }
    }

    /**
     * @param string $path
     * @return int
     * @throws GetError
     */
    static function getGroup(string $path): int
    {
        $group = filegroup($path);

        if ($group === false) {
            throw new GetError('Konnte Gruppe von ' . $path . ' nicht ermitteln!');
        }

        return $group;
    }

    /**
     * @param string $path
     * @param int $group
     * @throws SetError
     */
    static function setGroup(string $path, int $group)
    {
        if (!chgrp($path, $group)) {
            throw new SetError('Konnte Gruppe von ' . $path . ' nicht setzen!');
        }
    }

    /**
     * @param string $path
     * @param string $data
     * @param bool $overwrite
     * @throws CreateError
     * @throws FileExistsError
     */
    static function save(string $path, string $data, bool $overwrite = false)
    {
        if (
            !$overwrite &&
            file_exists($path)
        ) {
            throw new FileExistsError('Datei ' . $path . ' existiert bereits!');
        }

        if (!self::isWritable($path, true)) {
            throw new CreateError('Datei ' . $path . ' ist nicht schreibbar!');
        }

        if (file_put_contents($path, $data) === false) {
            throw new CreateError('Datei ' . $path . ' kann nicht erstellt werden!');
        }
    }

    /**
     * @param string $path
     * @param bool $overwrite
     * @return bool
     */
    static function isWritable(string $path, bool $overwrite = false): bool
    {
        if (file_exists($path)) {
            if (!is_writable($path)) {
                return false;
            }

            if (
                $overwrite === true ||
                (
                    is_array($overwrite) &&
                    in_array($path, $overwrite)
                )
            ) {
                return true;
            }

            return false;
        }

        return is_writable(self::getDir($path));
    }

    /**
     * @todo Refactor to files only array or null
     * @param string $dir
     * @param null|string|array $files
     * @throws DeleteError
     * @throws FileNotFound
     */
    static function delete(string $dir, $files = null)
    {
        $dir = Dir::setEndSlash($dir);

        if (
            is_array($files) ||
            is_null($files)
        ) {
            $deleteDir = false;

            if (is_null($files)) {
                $files = glob(Dir::escapeForGlob($dir) . "*");
                $deleteDir = true;
            }

            foreach ($files as $file) {
                $file = self::getFilename($file);
                self::delete($dir, $file);
            }

            if ($deleteDir) {
                $dirs = explode(DIRECTORY_SEPARATOR, Dir::removeEndSlash($dir));
                $lastDir = array_pop($dirs);
                $dir = Dir::setEndSlash(implode(DIRECTORY_SEPARATOR, $dirs));
                self::delete($dir, $lastDir);
            }

            return;
        }

        if (!file_exists($dir . $files)) {
            throw new FileNotFound('Datei ' . $dir . $files . ' existiert nicht!');
        }

        if (!self::isWritable($dir . $files, true)) {
            throw new DeleteError('Datei ' . $dir . $files . ' kann nicht gelöscht werden!');
        }

        passthru('rm ' . escapeshellarg($dir . $files) . ' -fR > /dev/null 2> /dev/null', $return);

        if ($return != 0) {
            throw new DeleteError('Datei ' . $files . ' konnte nicht gelöscht werden!');
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    static function getContentType(string $filename): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return (string)finfo_file($finfo, $filename);
    }

    /**
     * @param string $path
     * @param string $directorySeparator
     * @return string
     */
    static function getDir(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        if (mb_strrpos($path, $directorySeparator) === false) {
            return '';
        }

        return mb_substr($path, 0, mb_strrpos($path, $directorySeparator)+1);
    }

    /**
     * @param string $path
     * @param string $directorySeparator
     * @return string
     */
    static function getFilename(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        if (mb_strrpos($path, $directorySeparator) === false) {
            return $path;
        }

        return mb_substr($path, mb_strrpos($path, $directorySeparator)+1);
    }

    /**
     * @param string $path
     * @return string
     */
    static function getFileEnding(string $path): string
    {
        return mb_substr($path, mb_strrpos($path, '.')+1);
    }
}