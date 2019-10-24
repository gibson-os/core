<?php
namespace GibsonOS\Core\Exception;

use Throwable;

class ModuleNotFound extends AbstractException
{
    public function __construct($message = 'Modul nicht gefunden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}