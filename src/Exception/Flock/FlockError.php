<?php
namespace GibsonOS\Core\Exception\Flock;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class FlockError extends AbstractException
{
    public function __construct($message = 'Flock existiert bereits!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}