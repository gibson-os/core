<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use JsonException;
use JsonSerializable;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Query\SelectQuery;
use ReflectionException;

/**
 * @template T
 */
abstract class AbstractDatabaseStore extends AbstractStore
{
    protected Table $table;

    /**
     * @var Where[]
     */
    private array $wheres = [];

    private array $orderBy = [];

    protected string $tableName;

    protected SelectQuery $selectQuery;

    /**
     * @return class-string<T>
     */
    abstract protected function getModelClassName(): string;

    /**
     * @throws ClientException
     */
    public function __construct(private readonly DatabaseStoreWrapper $databaseStoreWrapper)
    {
        $modelClassName = $this->getModelClassName();
        /** @var ModelInterface $model */
        $model = new $modelClassName();
        $this->tableName = $model->getTableName();
        $this->table = $this->databaseStoreWrapper->getTableManager()->getTable($this->tableName);
    }

    public function setLimit(int $rows, int $from): self
    {
        parent::setLimit($rows, $from);

        $this->selectQuery->setLimit($rows, $from);

        return $this;
    }

    /**
     * @throws ClientException
     * @throws ReflectionException
     */
    protected function initQuery(): void
    {
        $this->selectQuery = (new SelectQuery($this->table, $this->getAlias()))
            ->setWheres($this->wheres)
            ->setLimit($this->getRows(), $this->getFrom())
            ->setOrders($this->getOrderBy())
        ;

        $this->databaseStoreWrapper->getChildrenQuery()->extend(
            $this->selectQuery,
            $this->getModelClassName(),
            $this->getExtends(),
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     *
     * @return array|iterable<T>
     */
    public function getList(): iterable
    {
        $this->initQuery();

        return $this->getModels();
    }

    /**
     * @throws SelectError
     * @throws ClientException
     * @throws ReflectionException
     */
    public function getCount(): int
    {
        $this->initQuery();
        $selects = $this->selectQuery->getSelects();
        $this->selectQuery->setSelects(['count' => sprintf('COUNT(%s)', $this->getCountField())]);

        $result = $this->databaseStoreWrapper->getClient()->execute($this->selectQuery);

        $this->selectQuery->setSelects($selects);

        return (int) ($result?->iterateRecords()->current()->get('count')->getValue() ?? 0);
    }

    protected function getCountField(): string
    {
        return '`' . $this->tableName . '`.`id`';
    }

    protected function getDefaultOrder(): string
    {
        return '`' . $this->tableName . '`.`id`';
    }

    protected function getDefaultOrderDirection(): OrderDirection
    {
        return OrderDirection::ASC;
    }

    protected function getAlias(): ?string
    {
        return null;
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
        $this->wheres[] = new Where($where, $parameters);

        return $this;
    }

    protected function setWheres(): void
    {
    }

    public function setSortByExt(array $sort): self
    {
        $mapping = $this->getOrderMapping();

        if (count($mapping) === 0) {
            $this->orderBy = [];

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

            $orderBy[$mapping[$sortItem['property']]] = mb_strtolower($sortItem['direction'] ?? 'asc') === 'asc'
                ? OrderDirection::ASC
                : OrderDirection::DESC;
        }

        $this->orderBy = $orderBy;

        return $this;
    }

    protected function getOrderBy(): array
    {
        return $this->orderBy ?: [$this->getDefaultOrder() => $this->getDefaultOrderDirection()];
    }

    /**
     * @return ChildrenMapping[]
     */
    protected function getExtends(): array
    {
        return [];
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     *
     * @return array|iterable<T>
     */
    protected function getModels(): iterable
    {
        $result = $this->databaseStoreWrapper->getClient()->execute($this->selectQuery);

        foreach ($result?->iterateRecords() ?? [] as $record) {
            yield $this->getModel($record);
        }
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     *
     * @return T
     */
    protected function getModel(Record $record, string $prefix = ''): AbstractModel
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

        $this->databaseStoreWrapper->getModelManager()->loadFromRecord($record, $model, $prefix);

        return $model;
    }

    public function getDatabaseStoreWrapper(): DatabaseStoreWrapper
    {
        return $this->databaseStoreWrapper;
    }
}
