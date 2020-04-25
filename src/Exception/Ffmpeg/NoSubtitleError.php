<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Ffmpeg;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class NoSubtitleError extends AbstractException
{
    public function __construct($message = 'Media hat keinen Untertitel Stream!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
