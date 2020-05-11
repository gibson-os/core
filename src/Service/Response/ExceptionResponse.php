<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Exception\RequestError;
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
        $headers = [
            'X-Exception' => var_export($this->exception),
            'X-Exception-Trace' => implode(PHP_EOL, $this->exception->getTrace()),
        ];

        if ($this->requestService->isAjax()) {
            $headers['Content-Type'] = 'text/json; charset=UTF-8';
        }

        return $headers;
    }

    public function getBody(): string
    {
        try {
            if ($this->requestService->isAjax()) {
                return JsonUtility::encode([
                    'success' => false,
                    'failure' => true,
                    'message' => $this->exception->getMessage(),
                    'trace' => $this->exception->getTrace(),
                ]);
            }
        } catch (RequestError $e) {
        }

        return $this->exception->getMessage();
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }
}
