<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception\Server;

use GibsonOS\Core\Exception\AbstractException;
use Throwable;

class SendError extends AbstractException
{
    public function __construct($message = 'Konnte nicht gesendet werden!', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
