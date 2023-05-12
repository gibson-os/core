<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
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

    public function isTransaction(mysqlDatabase $database = null): bool
    {
        $database = $this->getDatabase($database);

        return $database->isTransaction();
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @throws SelectError
     *
     * @return T[]
     */
    protected function getModels(mysqlTable $table, string $modelClassName): array
    {
        if ($table->selectPrepared() === false) {
            $exception = new SelectError($table->connection->error());
            $exception->setTable($table);

            throw $exception;
        }

        $models = [];

        if ($table->countRecords() === 0) {
            return $models;
        }

        do {
            $models[] = $this->getModel($table, $modelClassName);
        } while ($table->next());

        return $models;
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @return T
     */
    protected function getModel(mysqlTable $table, string $modelClassName): AbstractModel
    {
        $model = new $modelClassName();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @throws SelectError
     *
     * @return T
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $modelClassName,
        string $orderBy = null
    ): ModelInterface {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $table = $this->getTable($model->getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit(1)
            ->setOrderBy($orderBy)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError($table->connection->error() ?: 'No results!');
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, $modelClassName);
    }

    /**
     * @template T of AbstractModel
     *
     * @param class-string<T> $modelClassName
     *
     * @throws SelectError
     *
     * @return T[]
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $modelClassName,
        int $limit = null,
        int $offset = null,
        string $orderBy = null
    ): array {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $table = $this->getTable($model->getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit($limit, $offset)
            ->setOrderBy($orderBy)
        ;

        return $this->getModels($table, $modelClassName);
    }

    /**
     * @param class-string<ModelInterface> $modelClassName
     */
    protected function getAggregate(
        string $function,
        string $modelClassName,
        string $where = '',
        array $parameters = [],
    ): ?array {
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $table = $this->getTable($model->getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
        ;

        return $table->selectAggregatePrepared($function) ?: null;
    }

    protected function getTable(string $tableName, mysqlDatabase $database = null): mysqlTable
    {
        $database = $this->getDatabase($database);

        return new mysqlTable($database, $tableName);
    }

    protected function getRegexString(string $search, mysqlDatabase $database = null): string
    {
        $database = $this->getDatabase($database);

        return $database->getUnescapedRegexString($search);
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
