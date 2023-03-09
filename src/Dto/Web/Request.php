<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Web;

class Request
{
    private int $port = 80;

    /**
     * @var array<string, string>
     */
    private array $parameters = [];

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private ?Body $body = null;

    private ?string $cookieFile = null;

    public function __construct(private string $url)
    {
        if (mb_strpos($this->url, 'https://') === 0) {
            $this->port = 443;
        }
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Request
    {
        $this->url = $url;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): Request
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, string> $parameters
     */
    public function setParameters(array $parameters): Request
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameter(string $name): ?string
    {
        if (!isset($this->parameters[$name])) {
            return null;
        }

        return $this->parameters[$name];
    }

    public function setParameter(string $name, string $value): Request
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string> $headers
     */
    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;

        return $this;
    }

    public function setHeader(string $key, string $value): Request
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function getBody(): ?Body
    {
        return $this->body;
    }

    public function setBody(?Body $body): Request
    {
        $this->body = $body;

        return $this;
    }

    public function getCookieFile(): ?string
    {
        return $this->cookieFile;
    }

    public function setCookieFile(?string $cookieFile): Request
    {
        $this->cookieFile = $cookieFile;

        return $this;
    }
}
