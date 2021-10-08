<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\StatusCode;

class Response implements ResponseInterface
{
    private array $headers;

    public function __construct(private string $body, private int $code = StatusCode::OK, array $headers = [])
    {
        $this->headers = $headers;
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
