<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum;

enum NewRelicPrefix: string
{
    case NONE = '';
    case COMMAND_ARGUMENT = 'app.command.argument.';
    case COMMAND_OPTION = 'app.command.option.';
    case REQUEST_VALUE = 'app.request.value.';
}
