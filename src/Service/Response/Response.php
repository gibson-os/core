<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use Override;

class Response implements ResponseInterface
{
    private array $headers;

    public function __construct(private string $body, private HttpStatusCode $code = HttpStatusCode::OK, array $headers = [])
    {
        $this->headers = $headers;
    }

    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[Override]
    public function getBody(): string
    {
        return $this->body;
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return [];
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }
}
