<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Dto\Store\FilterInterface;
use GibsonOS\Core\Service\Response\AjaxResponse;

abstract class AbstractStore
{
    private int $rows = 0;

    private int $from = 0;

    abstract public function getList(): iterable;

    abstract public function getCount(): int;

    public function setLimit(int $rows, int $from): self
    {
        $this->rows = $rows;
        $this->from = $from;

        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param array<string, string[]> $filters
     */
    public function setFilters(array $filters): self
    {
        return $this;
    }

    /**
     * @return array<string, FilterInterface>
     */
    protected function getFilters(): array
    {
        return [];
    }

    public function setSortByExt(array $sort): self
    {
        return $this;
    }

    /**
     * @return array<string, string|string[]>
     */
    protected function getOrderMapping(): array
    {
        return [];
    }

    public function getAjaxResponse(): AjaxResponse
    {
        $data = $this->getList();
        $return = [
            'success' => true,
            'failure' => false,
            'data' => !is_array($data) ? iterator_to_array($data) : $data,
            'total' => $this->getCount(),
            'filters' => $this->getFilters(),
            'possibleOrders' => array_keys($this->getOrderMapping()),
        ];

        return new AjaxResponse($return);
    }
}
