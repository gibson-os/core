<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Ffmpeg;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class ConvertStatusError extends AbstractException
{
    public function __construct($message = 'Kein Konvertstatus gefunden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
