<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

class SetError extends AbstractException
{
    public function __construct($message = 'Konnte nicht gesetzt werden!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
