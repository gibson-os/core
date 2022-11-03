<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Flock;

use GibsonOS\Core\Exception\AbstractException;

class LockError extends AbstractException
{
    public function __construct($message = 'Lock error!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
