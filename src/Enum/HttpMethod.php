<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case PUT = 'PUT';
    case CONNECT = 'CONNECT';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case PATCH = 'PATCH';
}
