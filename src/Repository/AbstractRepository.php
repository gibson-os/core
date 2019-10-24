<?php
namespace GibsonOS\Core\Repository;


use GibsonOS\Core\Service\Registry;
use mysqlDatabase;
use mysqlTable;

class AbstractRepository
{
    /**
     * @param null|mysqlDatabase $database
     */
    public static function startTransaction(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->startTransaction();
    }

    /**
     * @param null|mysqlDatabase $database
     */
    public static function commit(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->commit();
    }

    /**
     * @param null|mysqlDatabase $database
     */
    public static function rollback(mysqlDatabase $database = null)
    {
        $database = self::getDatabase($database);
        $database->rollback();
    }

    /**
     * @param string $tableName
     * @param null|mysqlDatabase $database
     * @return mysqlTable
     */
    static protected function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = self::getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    /**
     * Maskiert und quotet
     *
     * Maskiert und qoutet einen Wert.
     *
     * @param string $value Wert
     * @param bool $withQuotes
     * @param null|mysqlDatabase $database
     * @return string
     */
    static protected function escape(string $value, bool $withQuotes = true, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->escape($value, $withQuotes);
    }

    /**
     * Gibt MySQL RexEx String zurück
     *
     * Gibt einen MySQL RexEx String zurück.
     *
     * @param string $search Suchstring
     * @param mysqlDatabase|null $database
     * @return string MySQL RexEx String
     */
    static protected function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->getRegexString($search);
    }

    /**
     * Maskiert und quotet
     *
     * Maskiert und qoutet ein Array mit Werten.
     *
     * @param array $pieces Werte
     * @param string $glue Trennzeichen
     * @param null|mysqlDatabase $database
     * @return string
     */
    static protected function implode(array $pieces, $glue = ',', mysqlDatabase $database = null): string
    {
        $database = self::getDatabase($database);

        return $database->implode($pieces, $glue);
    }

    /**
     * @param mysqlDatabase|null $database
     * @return mysqlDatabase
     */
    private static function getDatabase(mysqlDatabase $database = null): mysqlDatabase
    {
        if ($database instanceof mysqlDatabase) {
            return $database;
        } else {
            return Registry::getInstance()->get('database');
        }
    }
}