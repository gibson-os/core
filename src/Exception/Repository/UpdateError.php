<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Repository;

use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class UpdateError extends AbstractException
{
    private Table $table;

    public function __construct($message = 'Update war nicht erfolgreich!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setTable(Table $table): UpdateError
    {
        $this->table = $table;

        return $this;
    }
}
