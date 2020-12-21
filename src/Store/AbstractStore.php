<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

abstract class AbstractStore
{
    private int $rows = 0;

    private int $from = 0;

    abstract public function getList(): iterable;

    abstract public function getCount(): int;

    public function setLimit(int $rows, int $from): void
    {
        $this->rows = $rows;
        $this->from = $from;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function getFrom(): int
    {
        return $this->from;
    }
}
