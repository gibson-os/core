<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Web;

use GibsonOS\Core\Enum\HttpStatusCode;

class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly Request $request,
        private readonly HttpStatusCode $statusCode,
        private readonly array $headers,
        private readonly Body $body,
        private readonly string $cookieFile,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        if (!isset($this->headers[$name])) {
            return null;
        }

        return $this->headers[$name];
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function getCookieFile(): string
    {
        return $this->cookieFile;
    }
}
