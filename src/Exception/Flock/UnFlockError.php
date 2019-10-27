<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Flock;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class UnFlockError extends AbstractException
{
    public function __construct($message = 'Flock existiert nicht!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
