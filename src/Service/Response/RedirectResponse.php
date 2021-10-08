<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\StatusCode;

class RedirectResponse implements ResponseInterface
{
    public function __construct(private string $url, private int $code = StatusCode::MOVED_PERMANENTLY)
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getHeaders(): array
    {
        return ['Location' => $this->url];
    }

    public function getBody(): string
    {
        return '';
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }
}
