<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileExistsError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

class File extends AbstractService
{
    /**
     * @var Dir
     */
    private $dir;

    /**
     * File constructor.
     *
     * @param Dir $dir
     */
    public function __construct(Dir $dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function isReadable(string $filename): bool
    {
        return is_readable($filename);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @throws CreateError
     * @throws GetError
     * @throws SetError
     */
    public function copy(string $from, string $to): void
    {
        if (is_dir($from)) {
            $from = $this->dir->addEndSlash($from);
            $to = $this->dir->addEndSlash($to);

            if (!file_exists($to)) {
                $this->dir->create($to);
            }

            $chmod = $this->getPerms($from);
            $this->setPerms($to, $chmod);

            $owner = $this->getOwner($from);
            $this->setOwner($to, $owner);

            $group = $this->getGroup($from);
            $this->setGroup($to, $group);

            $files = glob($this->dir->escapeForGlob($from) . '*');

            if (is_bool($files)) {
                throw new GetError(sprintf('Verzeichnis %s kann nicht gelesen werden!', $from));
            }

            foreach ($files as $path) {
                $filename = $this->getFilename($path);
                $this->copy($path, $to . $filename);
            }
        } elseif (!copy($from, $to)) {
            throw new CreateError(sprintf('Konnte %s nicht nach %s kopieren!', $from, $to));
        }
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool   $overwrite
     * @param bool   $ignore
     *
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SetError
     */
    public function move(string $from, string $to, bool $overwrite = false, bool $ignore = false): void
    {
        if (is_dir($from)) {
            $from = $this->dir->addEndSlash($from);
            $to = $this->dir->addEndSlash($to);

            if (!file_exists($to)) {
                $this->dir->create($to);
            }

            $chmod = $this->getPerms($from);
            $this->setPerms($to, $chmod);

            $owner = $this->getOwner($from);
            $this->setOwner($to, $owner);

            $group = $this->getGroup($from);
            $this->setGroup($to, $group);

            $files = glob($this->dir->escapeForGlob($from) . '*');

            if (is_bool($files)) {
                throw new GetError(sprintf('Verzeichnis %s kann nicht gelesen werden!', $from));
            }

            foreach ($files as $path) {
                $filename = $this->getFilename($path);
                $this->move($path, $to . $filename, $overwrite, $ignore);
            }

            $this->delete($from, null);
        } elseif ($this->isWritable($to, $overwrite)) {
            if (!rename($from, $to)) {
                throw new CreateError(sprintf('Konnte %s nicht nach %s verschieben!', $from, $to));
            }
        }
    }

    /**
     * @param string $path
     *
     * @throws GetError
     *
     * @return int
     */
    public function getPerms(string $path): int
    {
        $chmod = fileperms($path);

        if ($chmod === false) {
            throw new GetError(sprintf('Konnte Dateiberechtigungen von %s nicht ermitteln!', $path));
        }

        return $chmod;
    }

    /**
     * @param string $path
     * @param int    $chmod
     *
     * @throws SetError
     */
    public function setPerms(string $path, int $chmod): void
    {
        if (!chmod($path, $chmod)) {
            throw new SetError(sprintf('Konnte Dateiberechtigungen von %s nicht setzen!', $path));
        }
    }

    /**
     * @param string $path
     *
     * @throws GetError
     *
     * @return int
     */
    public function getOwner(string $path): int
    {
        $owner = fileowner($path);

        if ($owner === false) {
            throw new GetError(sprintf('Konnte Eigentümer von %s nicht ermitteln!', $path));
        }

        return $owner;
    }

    /**
     * @param string $path
     * @param int    $owner
     *
     * @throws SetError
     */
    public function setOwner(string $path, int $owner): void
    {
        if (!chown($path, $owner)) {
            throw new SetError(sprintf('Konnte Eigentümer von %s nicht setzen!', $path));
        }
    }

    /**
     * @param string $path
     *
     * @throws GetError
     *
     * @return int
     */
    public function getGroup(string $path): int
    {
        $group = filegroup($path);

        if ($group === false) {
            throw new GetError(sprintf('Konnte Gruppe von %s nicht ermitteln!', $path));
        }

        return $group;
    }

    /**
     * @param string $path
     * @param int    $group
     *
     * @throws SetError
     */
    public function setGroup(string $path, int $group): void
    {
        if (!chgrp($path, $group)) {
            throw new SetError(sprintf('Konnte Gruppe von %s nicht setzen!', $path));
        }
    }

    /**
     * @param string $path
     * @param string $data
     * @param bool   $overwrite
     *
     * @throws CreateError
     * @throws FileExistsError
     */
    public function save(string $path, string $data, bool $overwrite = false): void
    {
        if (
            !$overwrite &&
            file_exists($path)
        ) {
            throw new FileExistsError(sprintf('Datei %s existiert bereits!', $path));
        }

        if (!$this->isWritable($path, true)) {
            throw new CreateError(sprintf('Datei %s ist nicht schreibbar!', $path));
        }

        if (file_put_contents($path, $data) === false) {
            throw new CreateError(sprintf('Datei %s kann nicht erstellt werden!', $path));
        }
    }

    /**
     * @param string $path
     * @param bool   $overwrite
     *
     * @return bool
     */
    public function isWritable(string $path, bool $overwrite = false): bool
    {
        if (file_exists($path)) {
            if (!is_writable($path)) {
                return false;
            }

            if ($overwrite) {
                return true;
            }

            return false;
        }

        return is_writable($this->getDir($path));
    }

    /**
     * @param string            $dir
     * @param string|array|null $files
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     *
     * @todo Refactor to files only array or null
     */
    public function delete(string $dir, $files = null): void
    {
        $dir = $this->dir->addEndSlash($dir);

        if (
            is_array($files) ||
            $files === null
        ) {
            $deleteDir = false;

            if ($files === null) {
                $files = glob($this->dir->escapeForGlob($dir) . '*');
                $deleteDir = true;

                if (is_bool($files)) {
                    throw new GetError(sprintf('Verzeichnis %s kann nicht gelesen werden!', $dir));
                }
            }

            foreach ($files as $file) {
                $file = $this->getFilename($file);
                $this->delete($dir, $file);
            }

            if ($deleteDir) {
                $dirs = explode(DIRECTORY_SEPARATOR, $this->dir->removeEndSlash($dir));
                $lastDir = array_pop($dirs);
                $dir = $this->dir->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs));
                $this->delete($dir, $lastDir);
            }

            return;
        }

        if (!file_exists($dir . $files)) {
            throw new FileNotFound(sprintf('Datei %s%s existiert nicht!', $dir, $files));
        }

        if (!$this->isWritable($dir . $files, true)) {
            throw new DeleteError(sprintf('Datei %s%s kann nicht gelöscht werden!', $dir, $files));
        }

        passthru(sprintf(
            'rm %s -fR > /dev/null 2> /dev/null',
            escapeshellarg($dir . $files)
        ), $return);

        if ($return != 0) {
            throw new DeleteError(sprintf('Datei %s konnte nicht gelöscht werden!', $files));
        }
    }

    /**
     * @param string $path
     * @param string $directorySeparator
     *
     * @return string
     */
    public function getDir(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        if (mb_strrpos($path, $directorySeparator) === false) {
            return '';
        }

        return mb_substr($path, 0, mb_strrpos($path, $directorySeparator) + 1);
    }

    /**
     * @param string $path
     * @param string $directorySeparator
     *
     * @return string
     */
    public function getFilename(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        if (mb_strrpos($path, $directorySeparator) === false) {
            return $path;
        }

        return mb_substr($path, mb_strrpos($path, $directorySeparator) + 1);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getFileEnding(string $path): string
    {
        return mb_substr($path, mb_strrpos($path, '.') + 1);
    }
}
