<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ExceptionResponse implements ResponseInterface
{
    public function __construct(
        private \Throwable $exception,
        private RequestService $requestService,
        private TwigService $twigService,
        private StatusCode $statusCode
    ) {
    }

    public function getCode(): int
    {
        $code = (int) ($this->exception->getCode() ?: 500);

        if (!$this->statusCode->isValidCode($code)) {
            $code = StatusCode::BAD_REQUEST;
        }

        return $code;
    }

    public function getHeaders(): array
    {
        $headers = [];

        if ($this->requestService->isAjax()) {
            $headers['Content-Type'] = 'text/json; charset=UTF-8';
        }

        return $headers;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getBody(): string
    {
        error_log($this->exception->getMessage());
        $traces = explode(PHP_EOL, $this->exception->getTraceAsString());

        foreach ($traces as $trace) {
            error_log($trace);
        }

        $exception = $this->getExceptionJson($this->exception);

        if ($this->requestService->isAjax()) {
            if ($this->exception instanceof AbstractException) {
                $data = [
                    'msg' => nl2br($this->exception->getMessage()),
                    'title' => $this->exception->getTitle(),
                    'type' => $this->exception->getType(),
                ];

                $data['extraParameters'] = $this->exception->getExtraParameters();
                $data['buttons'] = $this->exception->getButtons();

                if ($this->exception->getType() == AbstractException::PROMPT) {
                    $data['promptParameter'] = $this->exception->getPromptParameter();
                }

                return JsonUtility::encode([
                    'success' => false,
                    'failure' => true,
                    'data' => $data,
                ]);
            }

            return JsonUtility::encode([
                'success' => false,
                'failure' => true,
                'exception' => $exception,
            ]);
        }

        $response = new TwigResponse($this->twigService, '@core/exception.html.twig');
        $response->setVariable('exception', $exception);

        return $response->getBody();
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }

    private function getExceptionJson(\Throwable $exception): array
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
