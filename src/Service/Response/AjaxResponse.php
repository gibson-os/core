<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Utility\JsonUtility;

class AjaxResponse implements ResponseInterface
{
    /**
     * @var mixed
     */
    private $body;

    /**
     * @var int
     */
    private $code;

    /**
     * @param mixed $body
     */
    public function __construct($body, int $code = 200)
    {
        $this->body = $body;
        $this->code = $code;
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
