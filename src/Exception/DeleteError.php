<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use Throwable;

class DeleteError extends AbstractException
{
    public function __construct($message = 'Löschen nicht erfolgreich!', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
