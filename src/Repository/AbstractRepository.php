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

    /**
     * @throws SelectError
     *
     * @return ModelInterface[]
     */
    protected function getModels(mysqlTable $table, string $modelClassName): array
    {
        if ($table->selectPrepared() === false) {
            $exception = new SelectError($table->connection->error());
            $exception->setTable($table);

            throw $exception;
        }

        /** @var ModelInterface[] $models */
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
     * @throws SelectError
     */
    protected function getModel(mysqlTable $table, string $modelClassName): AbstractModel
    {
        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            $exception = new SelectError(sprintf(
                '%s is no instance of %s',
                $modelClassName,
                AbstractModel::class
            ));
            $exception->setTable($table);

            throw $exception;
        }

        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @throws SelectError
     */
    protected function fetchOne(
        string $where,
        array $parameters,
        string $modelClassName
    ): ModelInterface {
        /** @var ModelInterface $modelClassNameCopy */
        $modelClassNameCopy = $modelClassName;
        $table = $this->getTable($modelClassNameCopy::getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            $exception = new SelectError($table->connection->error() ?: 'No results!');
            $exception->setTable($table);

            throw $exception;
        }

        return $this->getModel($table, $modelClassName);
    }

    /**
     * @throws SelectError
     *
     * @return ModelInterface[]
     */
    protected function fetchAll(
        string $where,
        array $parameters,
        string $modelClassName,
        int $limit = null,
        int $offset = null,
        string $orderBy = null
    ): array {
        /** @var ModelInterface $modelClassNameCopy */
        $modelClassNameCopy = $modelClassName;
        $table = $this->getTable($modelClassNameCopy::getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
            ->setLimit($limit, $offset)
            ->setOrderBy($orderBy)
        ;

        return $this->getModels($table, $modelClassName);
    }

    protected function getAggregate(
        string $function,
        string $where,
        array $parameters,
        string $modelClassName = ModelInterface::class
    ): ?array {
        /** @var ModelInterface $modelClassNameCopy */
        $modelClassNameCopy = $modelClassName;
        $table = $this->getTable($modelClassNameCopy::getTableName())
            ->setWhere($where)
            ->setWhereParameters($parameters)
        ;

        return $table->selectAggregatePrepared($function);
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
