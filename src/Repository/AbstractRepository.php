<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

class AbstractRepository
{
    public static function startTransaction(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->startTransaction();
    }

    public static function commit(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->commit();
    }

    public static function rollback(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->rollback();
    }

    protected static function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = self::getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    protected static function escape(string $value, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->escape($value);
    }

    protected static function escapeWithoutQuotes(string $value, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->escapeWithoutQuotes($value);
    }

    protected static function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->getRegexString($search);
    }

    /**
     * @param string $glue
     */
    protected static function implode(array $pieces, $glue = ',', mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->implode($pieces, $glue);
    }

    private static function getDatabase(mysqlDatabase $database = null): mysqlDatabase
    {
        if ($database instanceof mysqlDatabase) {
            return $database;
        }

        return mysqlRegistry::getInstance()->get('database');
    }
}
