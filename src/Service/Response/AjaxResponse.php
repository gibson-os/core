<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Utility\JsonUtility;

readonly class AjaxResponse implements ResponseInterface
{
    public function __construct(
        private mixed $body,
        private HttpStatusCode $code = HttpStatusCode::OK
    ) {
    }

    public function getCode(): HttpStatusCode
    {
        return $this->code;
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => 'text/json; charset=UTF-8'];
    }

    public function getBody(): string
    {
        return JsonUtility::encode($this->body);
    }

    public function getRequiredHeaders(): array
    {
        return ['X-REQUESTED-WITH' => 'XMLHttpRequest'];
    }
}
