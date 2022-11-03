<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

class FileNotFound extends AbstractException
{
    public function __construct($message = 'Datei konnte nicht gefunden werden!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
