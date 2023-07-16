<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Response;

use CurlHandle;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Service\WebService;

class WebResponse implements ResponseInterface
{
    private ?CurlHandle $curlHandle = null;

    public function __construct(
        private readonly Request $request,
        private readonly WebService $webService,
    ) {
    }

    public function getCode(): HttpStatusCode
    {
        return $this->curlHandle === null
            ? HttpStatusCode::PROCESSING
            : HttpStatusCode::from((int) curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE))
        ;
    }

    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @throws WebException
     */
    public function getBody(): string
    {
        $this->curlHandle = $this->webService->requestWithOutput($this->request);

        return '';
    }

    public function getRequiredHeaders(): array
    {
        return [];
    }
}
