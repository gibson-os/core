<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use GibsonOS\Core\Enum\HttpStatusCode;
use Throwable;

class LoginRequired extends AbstractException
{
    public function __construct($message = 'Login is required!', ?Throwable $previous = null)
    {
        parent::__construct($message, HttpStatusCode::UNAUTHORIZED->value, $previous);
    }
}
