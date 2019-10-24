<?php
namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Utility\File;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

class SqLite extends AbstractService
{
    /**
     * @var string
     */
    private $filename;
    /**
     * @var SQLite3
     */
    private $database;

    /**
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->database = new SQLite3($this->filename);
    }

    /**
     * @param string $query
     * @throws ExecuteError
     */
    public function execute(string $query)
    {
        if (!$this->database->exec($query)) {
            throw new ExecuteError();
        }
    }

    /**
     * @param string $query
     * @return SQLite3Stmt
     */
    public function prepare(string $query): SQLite3Stmt
    {
        return $this->database->prepare($query);
    }

    /**
     * @param int $milliSeconds
     * @throws ExecuteError
     */
    public function busyTimeout(int $milliSeconds)
    {
        if (!$this->database->busyTimeout($milliSeconds)) {
            throw new ExecuteError();
        }
    }

    /**
     * @throws ExecuteError
     */
    public function close()
    {
        if (!$this->database->close()) {
            throw new ExecuteError();
        }
    }

    /**
     * @param string $query
     * @return SQLite3Result
     * @throws ExecuteError
     */
    public function query(string $query): SQLite3Result
    {
        $result = $this->database->query($query);

        if ($result === false) {
            throw new ExecuteError();
        }

        return $result;
    }

    /**
     * @param string $query
     * @return mixed
     * @throws ExecuteError
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
     * @param string $name
     * @param string $createQuery
     * @throws ExecuteError
     */
    public function addTableIfNotExists(string $name, string $createQuery)
    {
        if ($this->hasTable($name)) {
            return;
        }

        $this->execute($createQuery);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTable(string $name): bool
    {
        if ($this->database->querySingle("SELECT * FROM sqlite_master WHERE type='table' AND tbl_name='" . SQLite3::escapeString($name) . "'")) {
            return true;
        }

        return false;
    }

    /**
     * @return SQLite3
     */
    public function getDatabase(): SQLite3
    {
        return $this->database;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        if (!is_writable(File::getDir($this->filename))) {
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

    /**
     * @return bool
     */
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