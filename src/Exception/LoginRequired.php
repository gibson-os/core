<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use GibsonOS\Core\Utility\StatusCode;

class LoginRequired extends AbstractException
{
    public function __construct($message = 'Login is required!', \Throwable $previous = null)
    {
        parent::__construct($message, StatusCode::UNAUTHORIZED, $previous);
    }
}
