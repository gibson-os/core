<?php
namespace GibsonOS\Core\Store;

abstract class AbstractStore
{
    /**
     * @var int
     */
    private $rows = 0;
    /**
     * @var int
     */
    private $from = 0;

    /**
     * @return array[]
     */
    abstract public function getList();

    /**
     * @return int
     */
    abstract public function getCount();

    /**
     * @param int $rows
     * @param int $from
     */
    public function setLimit($rows, $from)
    {
        $this->rows = $rows;
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }
}