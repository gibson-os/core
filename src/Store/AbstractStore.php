<?php
declare(strict_types=1);

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

    abstract public function getList(): iterable;

    abstract public function getCount(): int;

    public function setLimit(int $rows, int $from): void
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
