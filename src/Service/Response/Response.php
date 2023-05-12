<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;

class Response implements ResponseInterface
{
    private array $headers;

    public function __construct(private string $body, private HttpStatusCode $code = HttpStatusCode::OK, array $headers = [])
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

    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }
}
