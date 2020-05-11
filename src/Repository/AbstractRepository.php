<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use InvalidArgumentException;
use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

abstract class AbstractRepository
{
    public function startTransaction(mysqlDatabase $database = null): void
    {
        $database = $this->getDatabase($database);
        $database->startTransaction();
    }

    public function commit(mysqlDatabase $database = null): void
    {
        $database = $this->getDatabase($database);
        $database->commit();
    }

    public function rollback(mysqlDatabase $database = null): void
    {
        $database = $this->getDatabase($database);
        $database->rollback();
    }

    protected function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = $this->getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    protected function escape(string $value, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->escape($value);
    }

    protected function escapeWithoutQuotes(string $value, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->escapeWithoutQuotes($value);
    }

    protected function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->getRegexString($search);
    }

    protected function implode(array $pieces, string $glue = ',', mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->implode($pieces, $glue);
    }

    private function getDatabase(mysqlDatabase $database = null): mysqlDatabase
    {
        if ($database instanceof mysqlDatabase) {
            return $database;
        }

        $database = mysqlRegistry::getInstance()->get('database');

        if (!$database instanceof mysqlDatabase) {
            throw new InvalidArgumentException('Datenbank nicht in der Registry gefunden!');
        }

        return $database;
    }
}
