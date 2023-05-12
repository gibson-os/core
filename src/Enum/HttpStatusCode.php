<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum;

use OutOfBoundsException;

enum HttpStatusCode: int
{
    case CONTINUE = 100;
    case SWITCHING_PROTOCOLS = 101;
    case PROCESSING = 102;
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;
    case PARTIAL_CONTENT = 206;
    case MULTI_STATUS = 207;
    case ALREADY_REPOSRTED = 208;
    case IM_USED = 226;
    case MULTIPLE_CHOICES = 300;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case SEE_OTHER = 303;
    case NOT_MODIFIED = 304;
    case USE_PROXY = 305;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case GONE = 410;
    case LENGTH_REQUIRED = 411;
    case PRECONDITION_FAILED = 412;
    case REQUEST_ENTITY_TOO_LARGE = 413;
    case URI_TOO_LONG = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    case EXECTATION_FAILED = 417;
    case POLICY_NOT_FULLFILLED = 420;
    case MISDIRECTED_REQUEST = 421;
    case UNPROCESSABLE_ENTITY = 422;
    case LOCKED = 423;
    case FAILED_DEPENDENCY = 424;
    case UPGRADE_REQUIRED = 426;
    case PRECONDITION_REQUIRED = 428;
    case TOO_MANY_REQUESTS = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case VARIANT_ALSO_NEGOTIATES = 506;
    case INSUFFICIENT_STORAGE = 507;
    case LOOP_DETECTED = 508;
    case BANDWIDTH_LIMIT_EXCEEDED = 509;
    case NOT_EXTENDED = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;

    private const CODES = [
        self::CONTINUE->value => 'Continue',
        self::SWITCHING_PROTOCOLS->value => 'Switching Protocols',
        self::PROCESSING->value => 'Processing',

        self::OK->value => 'OK',
        self::CREATED->value => 'Created',
        self::ACCEPTED->value => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION->value => 'Non-Authoritative Information',
        self::NO_CONTENT->value => 'No Content',
        self::RESET_CONTENT->value => 'Reset Content',
        self::PARTIAL_CONTENT->value => 'Partial Content',
        self::MULTI_STATUS->value => 'Multi-Status',
        self::ALREADY_REPOSRTED->value => 'Already Reported',
        self::IM_USED->value => 'IM Used',

        self::MULTIPLE_CHOICES->value => 'MUltiple Choices',
        self::MOVED_PERMANENTLY->value => 'Moved Permanently',
        self::FOUND->value => 'Found',
        self::SEE_OTHER->value => 'See Other',
        self::NOT_MODIFIED->value => 'Not Modified',
        self::USE_PROXY->value => 'Use Proxy',
        self::TEMPORARY_REDIRECT->value => 'Temporary Redirect',
        self::PERMANENT_REDIRECT->value => 'Permanent Redirect',

        self::BAD_REQUEST->value => 'Bad Request',
        self::UNAUTHORIZED->value => 'Unauthorized',
        self::PAYMENT_REQUIRED->value => 'Payment Required',
        self::FORBIDDEN->value => 'Forbidden',
        self::NOT_FOUND->value => 'Not Found',
        self::METHOD_NOT_ALLOWED->value => 'Method Not Allowed',
        self::NOT_ACCEPTABLE->value => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED->value => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT->value => 'Request Time-out',
        self::CONFLICT->value => 'Conflict',
        self::GONE->value => 'Gone',
        self::LENGTH_REQUIRED->value => 'Length Required',
        self::PRECONDITION_FAILED->value => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE->value => 'Request Entity Too Large',
        self::URI_TOO_LONG->value => 'Request-URI Too Large',
        self::UNSUPPORTED_MEDIA_TYPE->value => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE->value => 'Requested range not satisfiable',
        self::EXECTATION_FAILED->value => 'Expectation Failed',
        self::POLICY_NOT_FULLFILLED->value => 'Policy Not Fulfilled',
        self::MISDIRECTED_REQUEST->value => 'Misdirected Request',
        self::UNPROCESSABLE_ENTITY->value => 'Unprocessable Entity',
        self::LOCKED->value => 'Locked',
        self::FAILED_DEPENDENCY->value => 'Failed Dependency',
        self::UPGRADE_REQUIRED->value => 'Upgrade Required',
        self::PRECONDITION_REQUIRED->value => 'Precondition Required',
        self::TOO_MANY_REQUESTS->value => 'Too Many Requests',
        self::REQUEST_HEADER_FIELDS_TOO_LARGE->value => 'Request Header Fields Too Large',
        self::UNAVAILABLE_FOR_LEGAL_REASONS->value => 'Unavailable For Legal Reasons',

        self::INTERNAL_SERVER_ERROR->value => 'Internal Server Error',
        self::NOT_IMPLEMENTED->value => 'Not Implemented',
        self::BAD_GATEWAY->value => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE->value => 'Service Unavailable',
        self::GATEWAY_TIMEOUT->value => 'Gateway Time-out',
        self::HTTP_VERSION_NOT_SUPPORTED->value => 'HTTP Version Not Supported',
        self::VARIANT_ALSO_NEGOTIATES->value => 'Variant Also Negotiates',
        self::INSUFFICIENT_STORAGE->value => 'Insufficient Storage',
        self::LOOP_DETECTED->value => 'Loop Detected',
        self::BANDWIDTH_LIMIT_EXCEEDED->value => 'Bandwidth Limit Exceeded',
        self::NOT_EXTENDED->value => 'Not Extended',
        self::NETWORK_AUTHENTICATION_REQUIRED->value => 'Network Authentication Required',
    ];

    /**
     * @throws OutOfBoundsException
     */
    public function getStatusHeader(): string
    {
        return 'HTTP/1.0 ' . $this->value . ' ' . self::CODES[$this->value];
    }
}
