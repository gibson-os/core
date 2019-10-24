<?php
namespace GibsonOS\Core\Exception\Server;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class ReceiveError extends AbstractException
{
    public function __construct($message = 'Daten konnten nicht empfangen werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}