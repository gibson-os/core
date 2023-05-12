<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use GibsonOS\Core\Enum\HttpStatusCode;
use Throwable;

class PermissionDenied extends AbstractException
{
    public function __construct($message = 'Permission Denied', Throwable $previous = null)
    {
        parent::__construct($message, HttpStatusCode::FORBIDDEN->value, $previous);
    }
}
