<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\GetError;
use mysqlDatabase;
use mysqlRegistry;
use mysqlTable;

abstract class AbstractDatabaseStore extends AbstractStore
{
    protected mysqlDatabase $database;

    protected mysqlTable $table;

    protected array $where = [];

    private ?string $orderBy;

    abstract protected function getTableName(): string;

    abstract protected function getCountField(): string;

    /**
     * @return string[]
     */
    abstract protected function getOrderMapping(): array;

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

        $this->table = new mysqlTable($this->database, $this->getTableName());
    }

    public function setLimit(int $rows, int $from): void
    {
        parent::setLimit($rows, $from);

        $this->table->setLimit($rows, $from);
    }

    /**
     * @throws GetError
     */
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

        if (
            empty($count) ||
            !isset($count[0])
        ) {
            throw new GetError('Anzahl konnte nicht ermittelt werden!');
        }

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

            $order = '`' . $this->database->escapeWithoutQuotes($sortItem['property']) . '`';

            if (array_key_exists('direction', $sortItem)) {
                $order .= ' ' . $this->database->escapeWithoutQuotes($sortItem['direction']);
            }

            $orderBy[] = $order;
        }

        $this->orderBy = empty($orderBy) ? null : implode(', ', $orderBy);
    }

    protected function getOrderBy(): ?string
    {
        return $this->orderBy;
    }
}
