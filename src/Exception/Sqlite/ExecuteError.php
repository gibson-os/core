<?php
namespace GibsonOS\Core\Exception\Sqlite;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class ExecuteError extends AbstractException
{
    public function __construct($message = 'Sqlite Befehl konnte nicht ausgeführt werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}