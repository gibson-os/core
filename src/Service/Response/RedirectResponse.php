<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\StatusCode;

class RedirectResponse implements ResponseInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $code;

    public function __construct(string $url, int $code = StatusCode::MOVED_PERMANENTLY)
    {
        $this->url = $url;
        $this->code = $code;
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
