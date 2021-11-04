<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use JsonSerializable;
use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

abstract class AbstractDatabaseStore extends AbstractStore
{
    protected mysqlDatabase $database;

    protected mysqlTable $table;

    private array $wheres = [];

    private array $whereParameters = [];

    private ?string $orderBy = null;

    /**
     * @return class-string
     */
    abstract protected function getModelClassName(): string;

    /**
     * @throws CreateError
     */
    public function __construct(mysqlDatabase $database = null)
    {
        if ($database === null) {
            $this->database = mysqlRegistry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }

        if (!$this->database instanceof mysqlDatabase) {
            throw new CreateError('Kein Datenbank Objekt vorhanden!');
        }

        /** @var AbstractModel $modelClassName */
        $modelClassName = $this->getModelClassName();
        $this->table = new mysqlTable($this->database, $modelClassName::getTableName());
    }

    public function setLimit(int $rows, int $from): void
    {
        parent::setLimit($rows, $from);

        $this->table->setLimit($rows, $from);
    }

    protected function initTable(): void
    {
        $this->wheres = [];
        $this->setWheres();
        $this->table
            ->setWhere($this->getWhereString())
            ->setOrderBy($this->getOrderBy())
            ->setWhere($this->getWhereString())
            ->setWhereParameters($this->getWhereParameters())
            ->setLimit(
                $this->getRows() === 0 ? null : $this->getRows(),
                $this->getFrom() === 0 ? null : $this->getFrom()
            )
        ;
    }

    protected function getTableName(): string
    {
        /** @var AbstractModel $modelClassName */
        $modelClassName = $this->getModelClassName();

        return $modelClassName::getTableName();
    }

    /**
     * @throws SelectError
     *
     * @return AbstractModel[]|iterable
     */
    public function getList(): iterable
    {
        $this->initTable();

        return $this->getModels();
    }

    /**
     * @throws SelectError
     */
    public function getCount(): int
    {
        $this->initTable();
        $this->table
            ->setOrderBy()
            ->setLimit()
        ;

        $count = $this->table->selectAggregatePrepared('COUNT(' . $this->getCountField() . ')');
        $this->table->setLimit(
            $this->getRows() === 0 ? null : $this->getRows(),
            $this->getFrom() === 0 ? null : $this->getFrom()
        );

        if ($count === null) {
            throw new SelectError($this->table->connection->error());
        }

        if (
            empty($count) ||
            !isset($count[0])
        ) {
            return 0;
        }

        return (int) $count[0];
    }

    public function getWhereParameters(): array
    {
        return $this->whereParameters;
    }

    protected function getCountField(): string
    {
        return '`' . $this->getTableName() . '`.`id`';
    }

    protected function getDefaultOrder(): string
    {
        return '`' . $this->getTableName() . '`.`id`';
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping(): array
    {
        return [];
    }

    protected function addWhere(string $where, array $parameters = []): void
    {
        $this->wheres[] = $where;
        $this->whereParameters = array_merge($this->whereParameters, $parameters);
    }

    protected function setWheres(): void
    {
    }

    protected function getWhereString(): ?string
    {
        if (count($this->wheres) === 0) {
            return null;
        }

        return '(' . implode(') AND (', $this->wheres) . ')';
    }

    public function setSortByExt(array $sort): void
    {
        $mapping = $this->getOrderMapping();

        if (count($mapping) === 0) {
            $this->orderBy = null;

            return;
        }

        $orderBy = [];

        foreach ($sort as $sortItem) {
            if (!array_key_exists('property', $sortItem)) {
                continue;
            }

            if (!isset($mapping[$sortItem['property']])) {
                continue;
            }

            $order = '`' . $sortItem['property'] . '`';

            if (array_key_exists('direction', $sortItem)) {
                $order .= ' ' . (mb_strtolower($sortItem['direction']) === 'asc' ? 'ASC' : 'DESC');
            }

            $orderBy[] = $order;
        }

        $this->orderBy = empty($orderBy) ? $this->getDefaultOrder() : implode(', ', $orderBy);
    }

    protected function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @throws SelectError
     *
     * @return AbstractModel[]|iterable
     */
    protected function getModels(): iterable
    {
        if ($this->table->selectPrepared() === false) {
            $exception = new SelectError();
            $exception->setTable($this->table);

            throw $exception;
        }

        if ($this->table->countRecords() === 0) {
            return;
        }

        do {
            yield $this->getModel();
        } while ($this->table->next());
    }

    /**
     * @throws SelectError
     */
    private function getModel(): AbstractModel
    {
        $abstractModelClassName = $this->getModelClassName();
        $model = new $abstractModelClassName();

        if (!$model instanceof AbstractModel) {
            $exception = new SelectError(sprintf(
                '%s is no instance of %s',
                $abstractModelClassName,
                AbstractModel::class
            ));
            $exception->setTable($this->table);

            throw $exception;
        }

        if (!$model instanceof JsonSerializable) {
            $exception = new SelectError(sprintf('%s is not json serializable', $abstractModelClassName));
            $exception->setTable($this->table);

            throw $exception;
        }

        $model->loadFromMysqlTable($this->table);

        return $model;
    }
}
