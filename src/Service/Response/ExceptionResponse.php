<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\JsonUtility;
use Throwable;

class ExceptionResponse implements ResponseInterface
{
    /**
     * @var Throwable
     */
    private $exception;

    /**
     * @var RequestService
     */
    private $requestService;

    public function __construct(Throwable $exception, RequestService $requestService)
    {
        $this->exception = $exception;
        $this->requestService = $requestService;
    }

    public function getCode(): int
    {
        return (int) ($this->exception->getCode() ?: 500);
    }

    public function getHeaders(): array
    {
        $headers = [];

        if ($this->requestService->isAjax()) {
            $headers['Content-Type'] = 'text/json; charset=UTF-8';
        }

        return $headers;
    }

    public function getBody(): string
    {
        if ($this->requestService->isAjax()) {
            return JsonUtility::encode([
                'success' => false,
                'failure' => true,
                'message' => $this->exception->getMessage(),
                'trace' => $this->exception->getTrace(),
            ]);
        }

        return $this->exception->getMessage();
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }
}
