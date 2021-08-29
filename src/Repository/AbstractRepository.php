<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
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

    /**
     * @throws DateTimeError
     * @throws SelectError
     * @return AbstractModel[]
     */
    public function getModels(mysqlTable $table, string $abstractModelClassName): array
    {
        if ($table->selectPrepared() === false) {
            $exception = new SelectError();
            $exception->setTable($table);

            throw $exception;
        }

        /** @var AbstractModel[] $models */
        $models = [];

        if ($table->countRecords() === 0) {
            return $models;
        }

        do {
            $model = new $abstractModelClassName();

            if (!$model instanceof AbstractModel) {
                $exception = new SelectError(sprintf(
                    '%s is no instance of %s',
                    $abstractModelClassName,
                    AbstractModel::class
                ));
                $exception->setTable($table);

                throw $exception;
            }

            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $abstractModelClassName = AbstractModel::class
    ): AbstractModel {
        /** @var AbstractModel $abstractModelClassNameCopy */
        $abstractModelClassNameCopy = $abstractModelClassName;
        $table = $this->getTable($abstractModelClassNameCopy::getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError();
            $exception->setTable($table);

            throw $exception;
        }

        /** @var AbstractModel $model */
        $model = new $abstractModelClassName();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return AbstractModel[]
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $abstractModelClassName = AbstractModel::class,
        int $limit = null,
        int $offset = null
    ): array {
        /** @var AbstractModel $abstractModelClassNameCopy */
        $abstractModelClassNameCopy = $abstractModelClassName;
        $table = $this->getTable($abstractModelClassNameCopy::getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit($limit, $offset)
        ;

        return $this->getModels($table, $abstractModelClassName);
    }

    protected function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = $this->getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    /**
     * @deprecated
     */
    protected function escape(string $value, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->escape($value);
    }

    /**
     * @deprecated
     */
    protected function escapeWithoutQuotes(string $value, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->escapeWithoutQuotes($value);
    }

    protected function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->getUnescapedRegexString($search);
    }

    /**
     * @deprecated
     */
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
