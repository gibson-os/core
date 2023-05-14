<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum;

enum Permission: int
{
    case DENIED = 1;  // 00001
    case READ = 2;    // 00010
    case WRITE = 4;   // 00100
    case DELETE = 8;  // 01000
    case MANAGE = 16;
}
