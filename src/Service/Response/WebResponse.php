<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response as ResponseDto;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Service\WebService;
use Override;

class WebResponse implements ResponseInterface
{
    private ?ResponseDto $headResponse = null;

    public function __construct(
        private readonly Request $request,
        private readonly WebService $webService,
        private ?array $headers = null,
        private ?HttpStatusCode $statusCode = null,
    ) {
    }

    #[Override]
    public function getCode(): HttpStatusCode
    {
        if ($this->statusCode instanceof HttpStatusCode) {
            return $this->statusCode;
        }

        $this->statusCode = $this->getHeadResponse()->getStatusCode();

        return $this->statusCode;
    }

    #[Override]
    public function getHeaders(): array
    {
        if ($this->headers !== null) {
            return $this->headers;
        }

        $this->headers = $this->getHeadResponse()->getHeaders();

        return $this->headers;
    }

    /**
     * @throws WebException
     */
    #[Override]
    public function getBody(): string
    {
        $this->webService->requestWithOutput($this->request);

        return '';
    }

    #[Override]
    public function getRequiredHeaders(): array
    {
        return [];
    }

    private function getHeadResponse(): ResponseDto
    {
        if ($this->headResponse instanceof ResponseDto) {
            return $this->headResponse;
        }

        $headRequest = clone $this->request;
        $headRequest->setMethod(HttpMethod::HEAD);

        try {
            $this->headResponse = $this->webService->request($headRequest);
        } catch (WebException $exception) {
            $this->headResponse = new ResponseDto($headRequest, HttpStatusCode::NOT_FOUND, [], new Body(), '');
        }

        return $this->headResponse;
    }
}
