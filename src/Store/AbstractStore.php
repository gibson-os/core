<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Dto\Store\Filter;
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

    public function setFilters(array $filters): self
    {
        return $this;
    }

    /**
     * @return array<string, Filter>
     */
    protected function getFilters(): array
    {
        return [];
    }

    public function setSortByExt(array $sort): self
    {
        return $this;
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
        ];

        return new AjaxResponse($return);
    }
}
