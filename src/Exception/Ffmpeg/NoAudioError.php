<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Ffmpeg;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class NoAudioError extends AbstractException
{
    public function __construct($message = 'Media hat keinen Audio Stream!', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
