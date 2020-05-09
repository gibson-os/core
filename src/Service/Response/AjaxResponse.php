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
     * @param mixed $body
     */
    public function __construct($body)
    {
        $this->body = $body;
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
        return ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'];
    }
}
