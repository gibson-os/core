<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use Override;

class RedirectResponse implements ResponseInterface
{
    public function __construct(private string $url, private HttpStatusCode $code = HttpStatusCode::MOVED_PERMANENTLY)
    {
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }

    #[Override]
    public function getHeaders(): array
    {
        return ['Location' => $this->url];
    }

    #[Override]
    public function getBody(): string
    {
        return '';
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return [];
    }
}
