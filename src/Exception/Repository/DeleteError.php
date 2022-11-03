<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Repository;

use GibsonOS\Core\Exception\AbstractException;

class DeleteError extends AbstractException
{
    /**
     * @var \mysqlTable
     */
    private $table;

    public function __construct($message = 'Löschen war nicht erfolgreich!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \mysqlTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param \mysqlTable $table
     *
     * @return DeleteError
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
