<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Repository;

use GibsonOS\Core\Exception\AbstractException;

class UpdateError extends AbstractException
{
    /**
     * @var \mysqlTable
     */
    private $table;

    public function __construct($message = 'Update war nicht erfolgreich!', $code = 0, \Throwable $previous = null)
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
     * @return UpdateError
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
