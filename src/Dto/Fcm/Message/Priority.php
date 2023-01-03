<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm\Message;

enum Priority: string
{
    case NORMAL = 'normal';
    case HIGH = 'high';
}
