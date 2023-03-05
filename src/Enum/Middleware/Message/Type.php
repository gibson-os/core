<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum\Middleware\Message;

enum Type: string
{
    case NOTIFICATION = 'notification';
    case UPDATE = 'update';
}
