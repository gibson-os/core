<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use finfo;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileExistsError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;

class FileService
{
    public function __construct(private readonly DirService $dirService)
    {
    }

    public function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    public function isReadable(string $filename): bool
    {
        return is_readable($filename);
    }

    /**
     * @throws CreateError
     * @throws GetError
     * @throws SetError
     */
    public function copy(string $from, string $to): void
    {
        if (is_dir($from)) {
            $from = $this->dirService->addEndSlash($from);
            $to = $this->dirService->addEndSlash($to);

            if (!file_exists($to)) {
                $this->dirService->create($to);
            }

            //            $chmod = $this->getPerms($from);
            //            $this->setPerms($to, $chmod);
            //
            //            $owner = $this->getOwner($from);
            //            $this->setOwner($to, $owner);
            //
            //            $group = $this->getGroup($from);
            //            $this->setGroup($to, $group);

            foreach ($this->dirService->getFiles($from) as $path) {
                $filename = $this->getFilename($path);
                $this->copy($path, $to . $filename);
            }
        } elseif (!copy($from, $to)) {
            throw new CreateError(sprintf('Konnte %s nicht nach %s kopieren!', $from, $to));
        }
    }

    /**
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws SetError
     */
    public function move(string $from, string $to, bool $overwrite = false, bool $ignore = false): void
    {
        if (is_dir($from)) {
            $from = $this->dirService->addEndSlash($from);
            $to = $this->dirService->addEndSlash($to);

            if (!file_exists($to)) {
                $this->dirService->create($to);
            }

            //            $chmod = $this->getPerms($from);
            //            $this->setPerms($to, $chmod);
            //
            //            $owner = $this->getOwner($from);
            //            $this->setOwner($to, $owner);
            //
            //            $group = $this->getGroup($from);
            //            $this->setGroup($to, $group);

            foreach ($this->dirService->getFiles($from) as $path) {
                $filename = $this->getFilename($path);
                $this->move($path, $to . $filename, $overwrite, $ignore);
            }

            $this->delete($from);
        } elseif ($this->isWritable($to, $overwrite)) {
            if (!rename($from, $to)) {
                throw new CreateError(sprintf('Konnte %s nicht nach %s verschieben!', $from, $to));
            }
        } else {
            throw new CreateError(sprintf('%s is not writable!', $to));
        }
    }

    /**
     * @throws GetError
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
     * @throws SetError
     */
    public function setPerms(string $path, int $chmod): void
    {
        if (!chmod($path, $chmod)) {
            throw new SetError(sprintf('Konnte Dateiberechtigungen von %s nicht setzen!', $path));
        }
    }

    /**
     * @throws GetError
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
     * @throws SetError
     */
    public function setOwner(string $path, int $owner): void
    {
        if (!chown($path, $owner)) {
            throw new SetError(sprintf('Konnte Eigentümer von %s nicht setzen!', $path));
        }
    }

    /**
     * @throws GetError
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
     * @throws SetError
     */
    public function setGroup(string $path, int $group): void
    {
        if (!chgrp($path, $group)) {
            throw new SetError(sprintf('Konnte Gruppe von %s nicht setzen!', $path));
        }
    }

    /**
     * @throws CreateError
     * @throws FileExistsError
     */
    public function save(string $path, string $data, bool $overwrite = false): void
    {
        if (
            !$overwrite
            && file_exists($path)
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
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     *
     * @todo Refactor to files only array or null
     */
    public function delete(string $dir, string|array $files = null): void
    {
        $dir = $this->dirService->addEndSlash($dir);

        if (
            is_array($files)
            || $files === null
        ) {
            $deleteDir = false;

            if ($files === null) {
                $files = $this->dirService->getFiles($dir);
                $deleteDir = true;
            }

            foreach ($files as $file) {
                $file = $this->getFilename($file);
                $this->delete($dir, $file);
            }

            if ($deleteDir) {
                $dirs = explode(DIRECTORY_SEPARATOR, $this->dirService->removeEndSlash($dir));
                $lastDir = array_pop($dirs);
                $dir = $this->dirService->addEndSlash(implode(DIRECTORY_SEPARATOR, $dirs));
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
            escapeshellarg($dir . $files),
        ), $return);

        if ($return != 0) {
            throw new DeleteError(sprintf('Datei %s konnte nicht gelöscht werden!', $files));
        }
    }

    public function getDir(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        if (mb_strrpos($path, $directorySeparator) === false) {
            return '';
        }

        return mb_substr($path, 0, (mb_strrpos($path, $directorySeparator) ?: -1) + 1);
    }

    public function getFilename(string $path, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        return mb_substr($path, (mb_strrpos($path, $directorySeparator) ?: -1) + 1);
    }

    public function getFileEnding(string $path): string
    {
        $filename = $this->getFilename($path);

        if (mb_strrpos($filename, '.') === false) {
            return $filename;
        }

        return mb_substr($path, (mb_strrpos($path, '.') ?: -1) + 1);
    }

    /**
     * @throws GetError
     */
    public function getContentType(string $filename): string
    {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!$fileInfo instanceof finfo) {
            throw new GetError(sprintf('Content Type für %s konnte nicht ermittelt werden!', $filename));
        }

        return (string) finfo_file($fileInfo, $filename);
    }

    /**
     * @throws OpenError
     *
     * @return resource
     */
    public function open(string $filename, string $mode)
    {
        $file = fopen($filename, $mode);

        if (is_bool($file)) {
            throw new OpenError(sprintf('Datei "%s" konnte nicht geöffnet werden!', $filename));
        }

        return $file;
    }

    /**
     * @param resource $fileHandle
     */
    public function close($fileHandle): bool
    {
        return fclose($fileHandle);
    }

    /**
     * @throws OpenError
     */
    public function readLastLine(string $filename): string
    {
        $file = $this->open($filename, 'r');
        $line = '';
        $cursor = -1;

        fseek($file, $cursor, SEEK_END);
        $char = fgetc($file);

        while ($char === "\n" || $char === "\r") {
            fseek($file, $cursor--, SEEK_END);
            $char = fgetc($file);
        }

        while (
            $char !== false
            && $char !== "\n"
            && $char !== "\r"
        ) {
            $line = $char . $line;
            fseek($file, $cursor--, SEEK_END);
            $char = fgetc($file);
        }

        $this->close($file);

        return $line;
    }
}
