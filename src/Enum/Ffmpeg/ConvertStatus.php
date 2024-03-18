<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum\Ffmpeg;

enum ConvertStatus: string
{
    case ERROR = 'error';
    case WAIT = 'wait';
    case GENERATE = 'generate';
    case GENERATED = 'generated';
}
