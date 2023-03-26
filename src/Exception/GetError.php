<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use Throwable;

class GetError extends AbstractException
{
    public function __construct($message = 'Konnte nicht geholt werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
