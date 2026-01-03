<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Utility\JsonUtility;
use Override;

readonly class AjaxResponse implements ResponseInterface
{
    public function __construct(
        private mixed $body,
        private HttpStatusCode $code = HttpStatusCode::OK,
    ) {
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }

    #[Override]
    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json; charset=UTF-8'];
    }

    #[Override]
    public function getBody(): string
    {
        return JsonUtility::encode($this->body) ?: '';
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return ['X-REQUESTED-WITH' => 'XMLHttpRequest'];
    }
}
