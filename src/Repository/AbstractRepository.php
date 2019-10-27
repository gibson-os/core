<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

class AbstractRepository
{
    /**
     * @param mysqlDatabase|null $database
     */
    public static function startTransaction(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->startTransaction();
    }

    /**
     * @param mysqlDatabase|null $database
     */
    public static function commit(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->commit();
    }

    /**
     * @param mysqlDatabase|null $database
     */
    public static function rollback(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->rollback();
    }

    /**
     * @param string             $tableName
     * @param mysqlDatabase|null $database
     *
     * @return mysqlTable
     */
    protected static function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = self::getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    /**
     * @param string             $value
     * @param mysqlDatabase|null $database
     *
     * @return string
     */
    protected static function escape(string $value, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->escape($value);
    }

    /**
     * @param string             $value
     * @param mysqlDatabase|null $database
     *
     * @return string
     */
    protected static function escapeWithoutQuotes(string $value, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->escapeWithoutQuotes($value);
    }

    /**
     * @param string             $search
     * @param mysqlDatabase|null $database
     *
     * @return string
     */
    protected static function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->getRegexString($search);
    }

    /**
     * @param array              $pieces
     * @param string             $glue
     * @param mysqlDatabase|null $database
     *
     * @return string
     */
    protected static function implode(array $pieces, $glue = ',', mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->implode($pieces, $glue);
    }

    /**
     * @param mysqlDatabase|null $database
     *
     * @return mysqlDatabase
     */
    private static function getDatabase(mysqlDatabase $database = null): mysqlDatabase
    {
        if ($database instanceof mysqlDatabase) {
            return $database;
        }

        return mysqlRegistry::getInstance()->get('database');
    }
}
