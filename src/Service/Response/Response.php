<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\StatusCode;

class Response implements ResponseInterface
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $code;

    public function __construct(string $body, int $code = StatusCode::OK, array $headers = [])
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->code = $code;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }

    public function getCode(): int
    {
        return $this->code;
    }
}