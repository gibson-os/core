<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Sqlite;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class ReadError extends AbstractException
{
    public function __construct($message = 'Sqlite kann nicht gelesen werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
