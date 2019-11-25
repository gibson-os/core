<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

abstract class AbstractDatabaseStore extends AbstractStore
{
    /**
     * @var mysqlDatabase
     */
    protected $database;

    /**
     * @var mysqlTable
     */
    protected $table;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var string|null
     */
    private $orderBy;

    abstract protected function getTableName(): string;

    abstract protected function getCountField(): string;

    /**
     * @return string[]
     */
    abstract protected function getOrderMapping(): array;

    /**
     * Core_Abstract_Store constructor.
     */
    public function __construct(mysqlDatabase $database = null)
    {
        if (null === $database) {
            $this->database = mysqlRegistry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }

        $this->table = new mysqlTable($this->database, $this->getTableName());
    }

    public function setLimit(int $rows, int $from): void
    {
        parent::setLimit($rows, $from);

        $this->table->setLimit($rows, $from);
    }

    public function getCount(): int
    {
        $this->table->clearJoin();
        $this->table->setOrderBy();
        $this->table->setWhere($this->getWhere());
        $this->table->setLimit();

        $count = $this->table->selectAggregate('COUNT(' . $this->getCountField() . ')');
        $this->table->setLimit(
            $this->getRows() === 0 ? null : $this->getRows(),
            $this->getFrom() === 0 ? null : $this->getFrom()
        );

        return (int) $count[0];
    }

    protected function getWhere(): ?string
    {
        if (!count($this->where)) {
            return null;
        }

        return '(' . implode(') AND (', $this->where) . ')';
    }

    public function setSortByExt(array $sort): void
    {
        $mapping = $this->getOrderMapping();

        if (
            !is_array($sort) ||
            count($mapping) === 0
        ) {
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

            $order = '`' . $this->database->escapeWithoutQuotes($sortItem['property']) . '`';

            if (array_key_exists('direction', $sortItem)) {
                $order .= ' ' . $this->database->escapeWithoutQuotes($sortItem['direction']);
            }

            $orderBy[] = $order;
        }

        $this->orderBy = implode(', ', $orderBy);
    }

    protected function getOrderBy(): ?string
    {
        return $this->orderBy;
    }
}
