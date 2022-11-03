<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Ffmpeg;

use GibsonOS\Core\Exception\AbstractException;

class NoVideoError extends AbstractException
{
    public function __construct($message = 'Media hat keinen Video Stream!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
