<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Ffmpeg;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class ConvertException extends AbstractException
{
    public function __construct($message, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
