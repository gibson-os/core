<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Web;

class Response
{
    private Request $request;

    /**
     * @var array<string, string>
     */
    private array $headers;

    private Body $body;

    private string $cookieFile;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(Request $request, array $headers, Body $body, string $cookieFile)
    {
        $this->request = $request;
        $this->headers = $headers;
        $this->body = $body;
        $this->cookieFile = $cookieFile;
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
