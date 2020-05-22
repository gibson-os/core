<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\TwigService;
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

    /**
     * @var TwigService
     */
    private $twigService;

    public function __construct(Throwable $exception, RequestService $requestService, TwigService $twigService)
    {
        $this->exception = $exception;
        $this->requestService = $requestService;
        $this->twigService = $twigService;
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
                'exception' => $this->getExceptionJson($this->exception),
            ]);
        }

        $response = new TwigResponse($this->twigService, '@core/exception.html.twig');
        $response->setVariable('exception', $this->exception);

        return $response->getBody();
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }

    private function getExceptionJson(Throwable $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'previous' => $exception->getPrevious() === null ? null : $this->getExceptionJson($exception->getPrevious()),
            'trace' => $exception->getTrace(),
        ];
    }
}
