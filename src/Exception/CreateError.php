<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

class CreateError extends AbstractException
{
    public function __construct($message = 'Erstellung nicht erfolgreich!', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
