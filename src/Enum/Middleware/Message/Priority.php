<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum\Middleware\Message;

enum Priority: string
{
    case NORMAL = 'normal';
    case HIGH = 'high';
}
