<?php
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Sqlite\ExecuteError;

class SqLiteService
{
    private \SQLite3 $database;

    public function __construct(private readonly string $filename, private readonly FileService $fileService)
    {
        $this->database = new \SQLite3($this->filename);
    }

    /**
     * @throws ExecuteError
     */
    public function execute(string $query): void
    {
        if (!$this->database->exec($query)) {
            throw new ExecuteError();
        }
    }

    /**
     * @throws ExecuteError
     */
    public function prepare(string $query): \SQLite3Stmt
    {
        $statement = $this->database->prepare($query);

        if ($statement instanceof \SQLite3Stmt) {
            return $statement;
        }

        throw new ExecuteError('Query konnte nicht ausgeführt werden!');
    }

    /**
     * @throws ExecuteError
     */
    public function busyTimeout(int $milliSeconds): void
    {
        if (!$this->database->busyTimeout($milliSeconds)) {
            throw new ExecuteError();
        }
    }

    /**
     * @throws ExecuteError
     */
    public function close(): void
    {
        if (!$this->database->close()) {
            throw new ExecuteError();
        }
    }

    /**
     * @throws ExecuteError
     */
    public function query(string $query): \SQLite3Result
    {
        $result = $this->database->query($query);

        if ($result === false) {
            throw new ExecuteError();
        }

        return $result;
    }

    /**
     * @throws ExecuteError
     *
     * @return mixed
     */
    public function querySingle(string $query)
    {
        $result = $this->database->querySingle($query);

        if ($result === false) {
            throw new ExecuteError();
        }

        return $result;
    }

    /**
     * @throws ExecuteError
     */
    public function addTableIfNotExists(string $name, string $createQuery): void
    {
        if ($this->hasTable($name)) {
            return;
        }

        $this->execute($createQuery);
    }

    public function hasTable(string $name): bool
    {
        if ($this->database->querySingle("SELECT * FROM sqlite_master WHERE type='table' AND tbl_name='" . \SQLite3::escapeString($name) . "'")) {
            return true;
        }

        return false;
    }

    public function getDatabase(): \SQLite3
    {
        return $this->database;
    }

    public function isWritable(): bool
    {
        if (!is_writable($this->fileService->getDirService($this->filename))) {
            return false;
        }

        if (
            file_exists($this->filename) &&
            !is_writable($this->filename)
        ) {
            return false;
        }

        return true;
    }

    public function isReadable(): bool
    {
        if (
            !file_exists($this->filename) ||
            !is_readable($this->filename)
        ) {
            return false;
        }

        return true;
    }
}
