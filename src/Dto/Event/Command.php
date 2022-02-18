<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event;

enum Command: string
{
    case IF = 'if';
    case ELSE = 'else';
    case ELSE_IF = 'else_if';
    case WHILE = 'while';
    case DO_WHILE = 'do_while';
}
