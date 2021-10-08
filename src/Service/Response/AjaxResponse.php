<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;

class AjaxResponse implements ResponseInterface
{
    /**
     * @param mixed $body
     */
    public function __construct(private $body, private int $code = StatusCode::OK)
    {
    }

    public function getCode(): int
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
