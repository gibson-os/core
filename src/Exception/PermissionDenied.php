<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use GibsonOS\Core\Utility\StatusCode;

class PermissionDenied extends AbstractException
{
    public function __construct($message = 'Permission Denied', \Throwable $previous = null)
    {
        parent::__construct($message, StatusCode::FORBIDDEN, $previous);
    }
}
