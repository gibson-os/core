<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use Throwable;

class FileExistsError extends AbstractException
{
    public function __construct($message = 'Datei existiert bereits!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
