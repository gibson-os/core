<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Lock;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class LockException extends AbstractException
{
    public function __construct($message = 'Lock error!', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
