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

    /**
     * @var resource
     */
    private $body;

    /**
     * @param array<string, string> $headers
     * @param resource              $body
     */
    public function __construct(Request $request, array $headers, $body)
    {
        $this->request = $request;
        $this->headers = $headers;
        $this->body = $body;
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

    /**
     * @return resource
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
