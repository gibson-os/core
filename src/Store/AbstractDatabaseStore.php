<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use JsonException;
use JsonSerializable;
use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;
use ReflectionException;

/**
 * @template T
 */
abstract class AbstractDatabaseStore extends AbstractStore
{
    protected mysqlDatabase $database;

    protected mysqlTable $table;

    private array $wheres = [];

    private array $whereParameters = [];

    private ?string $orderBy = null;

    protected string $tableName;

    /**
     * @return class-string<T>
     */
    abstract protected function getModelClassName(): string;

    public function __construct(mysqlDatabase $database = null)
    {
        if ($database === null) {
            $this->database = mysqlRegistry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }

        $modelClassName = $this->getModelClassName();
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $this->tableName = $model->getTableName();
        $this->table = new mysqlTable($this->database, $this->tableName);
    }

    public function setLimit(int $rows, int $from): self
    {
        parent::setLimit($rows, $from);

        $this->table->setLimit($rows, $from);

        return $this;
    }

    protected function initTable(): void
    {
        $this->wheres = [];
        $this->whereParameters = [];
        $this->setWheres();
        $this->table->reset();
        $this->table
            ->setOrderBy($this->getOrderBy())
            ->setWhere($this->getWhereString())
            ->setWhereParameters($this->getWhereParameters())
            ->setLimit(
                $this->getRows() === 0 ? null : $this->getRows(),
                $this->getFrom() === 0 ? null : $this->getFrom(),
            )
        ;
    }

    /**
     * @throws ReflectionException
     * @throws SelectError
     * @throws JsonException
     *
     * @return array|iterable<T>
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
        $this->table->setLimit();

        $count = $this->table->selectAggregatePrepared('COUNT(' . $this->getCountField() . ')');
        $this->table->setLimit(
            $this->getRows() === 0 ? null : $this->getRows(),
            $this->getFrom() === 0 ? null : $this->getFrom(),
        );

        if ($count === null) {
            throw new SelectError($this->table->connection->error());
        }

        if (
            empty($count)
            || !isset($count[0])
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
        return '`' . $this->tableName . '`.`id`';
    }

    protected function getDefaultOrder(): string
    {
        return '`' . $this->tableName . '`.`id`';
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping(): array
    {
        return [];
    }

    protected function addWhere(string $where, array $parameters = []): self
    {
        $this->wheres[] = $where;
        $this->whereParameters = array_merge($this->whereParameters, $parameters);

        return $this;
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

    public function setSortByExt(array $sort): self
    {
        $mapping = $this->getOrderMapping();

        if (count($mapping) === 0) {
            $this->orderBy = null;

            return $this;
        }

        $orderBy = [];

        foreach ($sort as $sortItem) {
            if (!array_key_exists('property', $sortItem)) {
                continue;
            }

            if (!isset($mapping[$sortItem['property']])) {
                continue;
            }

            $order = $mapping[$sortItem['property']];

            if (array_key_exists('direction', $sortItem)) {
                $order .= ' ' . (mb_strtolower($sortItem['direction']) === 'asc' ? 'ASC' : 'DESC');
            }

            $orderBy[] = $order;
        }

        $this->orderBy = empty($orderBy) ? null : implode(', ', $orderBy);

        return $this;
    }

    protected function getOrderBy(): ?string
    {
        return $this->orderBy ?? $this->getDefaultOrder();
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     *
     * @return array|iterable<T>
     */
    protected function getModels(): iterable
    {
        if ($this->table->selectPrepared() === false) {
            $exception = new SelectError($this->table->connection->error());
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
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return T
     */
    protected function getModel(): AbstractModel
    {
        $modelClassName = $this->getModelClassName();
        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            $exception = new SelectError(sprintf(
                '%s is no instance of %s',
                $modelClassName,
                AbstractModel::class,
            ));
            $exception->setTable($this->table);

            throw $exception;
        }

        if (!$model instanceof JsonSerializable) {
            $exception = new SelectError(sprintf('%s is not json serializable', $modelClassName));
            $exception->setTable($this->table);

            throw $exception;
        }

        $model->loadFromMysqlTable($this->table);

        return $model;
    }
}
