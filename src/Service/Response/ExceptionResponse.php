<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Override;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use ValueError;

readonly class ExceptionResponse implements ResponseInterface
{
    public function __construct(
        private Throwable $exception,
        private RequestService $requestService,
        private TwigService $twigService,
    ) {
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        try {
            return HttpStatusCode::from($this->exception->getCode() ?: 500);
        } catch (ValueError) {
            return HttpStatusCode::BAD_REQUEST;
        }
    }

    #[Override]
    public function getHeaders(): array
    {
        $headers = [];

        if ($this->requestService->isAjax()) {
            $headers['Content-Type'] = 'application/json; charset=UTF-8';
        }

        return $headers;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws JsonException
     */
    #[Override]
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

                $data['extraParameters'] = (object) $this->exception->getExtraParameters();
                $data['buttons'] = $this->exception->getButtons();

                if ($this->exception->getType() == AbstractException::PROMPT) {
                    $data['promptParameter'] = $this->exception->getPromptParameter();
                }

                return JsonUtility::encode([
                    'success' => false,
                    'failure' => true,
                    'data' => $data,
                ]) ?: '';
            }

            return JsonUtility::encode([
                'success' => false,
                'failure' => true,
                'exception' => $exception,
            ]) ?: '';
        }

        $response = new TwigResponse($this->twigService, '@core/exception.html.twig');
        $response->setVariable('exception', $exception);

        return $response->getBody();
    }

    #[Override]
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
            'previous' => $exception->getPrevious() instanceof Throwable ? $this->getExceptionJson($exception->getPrevious()) : null,
            'trace' => $exception->getTrace(),
        ];
    }
}
