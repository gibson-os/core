<?php
namespace GibsonOS\Core\Exception;

use Throwable;

class SetError extends AbstractException
{
    public function __construct($message = 'Konnte nicht gesetzt werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}