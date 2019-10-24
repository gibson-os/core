<?php
namespace GibsonOS\Core\Store;

use GibsonOS\Core\Service\Registry;
use mysqlDatabase;
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
     * @var string
     */
    private $orderBy;

    /**
     * @return string
     */
    abstract protected function getTableName();

    /**
     * @return string
     */
    abstract protected function getCountField();

    /**
     * @return string[]
     */
    abstract protected function getOrderMapping();

    /**
     * Core_Abstract_Store constructor.
     * @param mysqlDatabase|null $database
     */
    public function __construct(mysqlDatabase $database = null)
    {
        if (is_null($database)) {
            $this->database = Registry::getInstance()->get('database');
        } else {
            $this->database = $database;
        }

        $this->table = new mysqlTable($this->database, $this->getTableName());
    }

    /**
     * @param int $rows
     * @param int $from
     */
    public function setLimit($rows, $from)
    {
        parent::setLimit($rows, $from);

        $this->table->setLimit($rows, $from);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $this->table->clearJoin();
        $this->table->setOrderBy(false);
        $this->table->setWhere($this->getWhere());
        $this->table->setLimit(false, false);

        $count = $this->table->selectAggregate('COUNT(' . $this->getCountField() . ')');
        $this->table->setLimit(
            $this->getRows() === 0 ? false : $this->getRows(),
            $this->getFrom() === 0 ? false : $this->getFrom()
        );

        return $count[0];
    }

    /**
     * @return string|null
     */
    protected function getWhere()
    {
        if (!count($this->where)) {
            return null;
        }

        return '(' . implode(') AND (', $this->where) . ')';
    }

    /**
     * @param array $sort
     */
    public function setSortByExt($sort)
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

            $order = '`' . $this->database->escape($sortItem['property'], false) . '`';

            if (array_key_exists('direction', $sortItem)) {
                $order .= ' ' . $this->database->escape($sortItem['direction'], false);
            }

            $orderBy[] = $order;
        }

        $this->orderBy = implode(', ', $orderBy);
    }

    /**
     * @return string
     */
    protected function getOrderBy()
    {
        return $this->orderBy;
    }
}