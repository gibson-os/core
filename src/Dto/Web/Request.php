<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Web;

class Request
{
    private string $url;

    private int $port = 80;

    /**
     * @var array<string, string>
     */
    private array $parameters = [];

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * @var resource|null
     */
    private $body;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
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

    /**
     * @return resource|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param resource|null $body
     */
    public function setBody($body): Request
    {
        $this->body = $body;

        return $this;
    }
}
