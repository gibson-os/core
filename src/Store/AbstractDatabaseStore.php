<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Dto\Store\Filter\Option;
use GibsonOS\Core\Exception\FilterException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\StoreException;
use GibsonOS\Core\Model\AbstractModel;
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

    /**
     * @var array<string, string[]>
     */
    private array $filters = [];

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
        /** @var AbstractModel $model */
        $model = new $modelClassName($this->databaseStoreWrapper->getModelWrapper());
        $this->tableName = $model->getTableName();
        $this->table = $this->databaseStoreWrapper->getTableManager()->getTable($this->tableName);
    }

    public function setLimit(int $rows, int $from): self
    {
        parent::setLimit($rows, $from);

        return $this;
    }

    /**
     * @param array<string, string[]> $filters
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @throws ClientException
     * @throws ReflectionException
     * @throws StoreException
     */
    protected function initQuery(): void
    {
        $filters = $this->filters === [] ? [] : $this->getFilters();
        $this->setWheres();
        $this->selectQuery = (new SelectQuery($this->table, $this->getAlias()))
            ->setWheres($this->wheres)
            ->setOrders($this->getOrderBy())
            ->setLimit($this->getRows(), $this->getFrom())
        ;

        foreach ($this->filters as $field => $value) {
            $filter = $filters[$field] ?? null;

            if ($filter === null) {
                throw new StoreException(sprintf(
                    'Filter "%s" not allowed. Possible filters: %s',
                    $field,
                    implode(', ', array_keys($this->getFilters())),
                ));
            }

            if (count($value) === 0) {
                continue;
            }

            $this->selectQuery->addWhere($filter->getWhere($field, $value, $this->databaseStoreWrapper));
        }

        $this->selectQuery = $this->databaseStoreWrapper->getChildrenQuery()->extend(
            $this->selectQuery,
            $this->getModelClassName(),
            $this->getExtends(),
        );
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     * @throws StoreException
     * @throws ClientException
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
     * @throws StoreException
     */
    public function getCount(): int
    {
        $rows = $this->getRows();
        $from = $this->getFrom();

        $this->setLimit(0, 0);
        $this->initQuery();

        $selects = $this->selectQuery->getSelects();
        $this->selectQuery->setSelects(['count' => sprintf('COUNT(%s)', $this->getCountField())]);

        $result = $this->databaseStoreWrapper->getClient()->execute($this->selectQuery);

        $this->selectQuery->setSelects($selects);
        $this->setLimit($rows, $from);

        return (int) ($result->iterateRecords()->current()->get('count')->getValue() ?? 0);
    }

    protected function getCountField(): string
    {
        return 'DISTINCT `' . ($this->getAlias() ?? $this->tableName) . '`.`id`';
    }

    /**
     * @param array<string, OrderDirection> $orders
     *
     * @throws ClientException
     * @throws RecordException
     * @throws StoreException
     * @throws ReflectionException
     *
     * @return Option[]
     */
    protected function getFilterOptions(string $nameSelect, string $valueSelect, bool $distinct, array $orders): array
    {
        $rows = $this->getRows();
        $from = $this->getFrom();

        $filters = $this->filters;
        $this
            ->setFilters([])
            ->setLimit(0, 0)
        ;
        $this->initQuery();

        $this->selectQuery
            ->setSelects(['name' => $nameSelect, 'value' => $valueSelect])
            ->setDistinct($distinct)
            ->setOrders($orders)
        ;

        $result = $this->getDatabaseStoreWrapper()->getClient()->execute($this->selectQuery);

        $this
            ->setFilters($filters)
            ->setLimit($rows, $from)
        ;

        return array_map(
            fn (Record $record) => new Option(
                (string) $record->get('name')->getValue(),
                (string) $record->get('value')->getValue(),
            ),
            iterator_to_array($result->iterateRecords()),
        );
    }

    /**
     * @param class-string $enumClassName
     *
     * @throws FilterException
     */
    protected function getEnumFilterOptions(string $enumClassName): array
    {
        if (!enum_exists($enumClassName)) {
            throw new FilterException(sprintf('Enum %s not found', $enumClassName));
        }

        $options = [];

        foreach ($enumClassName::cases() as $value) {
            $options[] = new Option($value->value, $value->name);
        }

        return $options;
    }

    /**
     * @return array<string, OrderDirection>
     */
    protected function getDefaultOrder(): array
    {
        return ['`' . ($this->getAlias() ?? $this->tableName) . '`.`id`' => OrderDirection::ASC];
    }

    /**
     * @return array<string, OrderDirection>
     */
    protected function getOrderExtension(): array
    {
        return [];
    }

    protected function getAlias(): ?string
    {
        return null;
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

        if ($mapping === []) {
            $this->orderBy = [];

            return $this;
        }

        $orderBy = [];

        foreach ($sort as $sortItem) {
            if (!array_key_exists('property', $sortItem)) {
                continue;
            }

            $sortMappings = $mapping[$sortItem['property']] ?? null;

            if ($sortMappings === null) {
                continue;
            }

            $sortMappings = is_array($sortMappings) ? $sortMappings : [$sortMappings];

            foreach ($sortMappings as $sortMapping) {
                $orderBy[$sortMapping] = mb_strtolower($sortItem['direction'] ?? 'asc') === 'asc'
                    ? OrderDirection::ASC
                    : OrderDirection::DESC;
            }
        }

        $this->orderBy = $orderBy;

        return $this;
    }

    protected function getOrderBy(): array
    {
        $orders = $this->orderBy ?: $this->getDefaultOrder();

        foreach ($this->getOrderExtension() as $orderExtensionField => $orderExtensionDirection) {
            $orders[$orderExtensionField] ??= $orderExtensionDirection;
        }

        return $orders;
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
        $models = [];

        foreach ($result->iterateRecords() as $record) {
            $primaryKey = implode(
                '-',
                $this->databaseStoreWrapper->getPrimaryKeyExtractor()->extractFromRecord(
                    $this->selectQuery->getTable(),
                    $record,
                ),
            );

            if (!isset($models[$primaryKey])) {
                $model = $this->getModel($record);
                $models[$primaryKey] = $model;
            }

            $this->databaseStoreWrapper->getChildrenMapper()->getChildrenModels(
                $record,
                $models[$primaryKey],
                $this->getExtends(),
            );
        }

        return array_values($models);
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
        $model = new $modelClassName($this->databaseStoreWrapper->getModelWrapper());

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

    protected function getDatabaseStoreWrapper(): DatabaseStoreWrapper
    {
        return $this->databaseStoreWrapper;
    }

    /**
     * @throws ClientException
     */
    protected function getTable(string $tableName): Table
    {
        return $this->databaseStoreWrapper->getTableManager()->getTable($tableName);
    }
}
