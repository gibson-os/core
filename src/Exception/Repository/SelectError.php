<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Repository;

use GibsonOS\Core\Exception\AbstractException;
use MDO\Dto\Table;
use Throwable;

class SelectError extends AbstractException
{
    private Table $table;

    public function __construct($message = 'Abfrage war nicht erfolgreich!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setTable(Table $table): SelectError
    {
        $this->table = $table;

        return $this;
    }
}
