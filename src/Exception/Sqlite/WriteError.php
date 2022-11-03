<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Sqlite;

use GibsonOS\Core\Exception\AbstractException;

class WriteError extends AbstractException
{
    public function __construct($message = 'Sqlite kann nicht beschrieben werden!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
