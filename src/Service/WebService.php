<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use CurlHandle;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\WebException;
use Psr\Log\LoggerInterface;

class WebService
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @throws WebException
     */
    public function get(Request $request): Response
    {
        return $this->request($request, HttpMethod::GET);
    }

    /**
     * @throws WebException
     */
    public function post(Request $request): Response
    {
        return $this->request($request, HttpMethod::POST);
    }

    /**
     * @throws WebException
     */
    public function head(Request $request): Response
    {
        return $this->request($request, HttpMethod::HEAD);
    }

    /**
     * @throws WebException
     */
    public function requestWithOutput(Request $request): CurlHandle
    {
        $curl = $this->initRequest($request);
        $this->setCookieFile($request, $curl);

        curl_setopt($curl, CURLOPT_HEADER, 1);

        if (!curl_exec($curl)) {
            throw new WebException(curl_error($curl));
        }

        return $curl;
    }

    /**
     * @throws WebException
     */
    public function request(Request $request, HttpMethod $method = null): Response
    {
        $curl = $this->initRequest($request, $method);

        $responseHandle = fopen('php://memory', 'r+');
        curl_setopt($curl, CURLOPT_FILE, $responseHandle);

        $cookieFile = $this->setCookieFile($request, $curl);

        if (!curl_exec($curl)) {
            throw new WebException(curl_error($curl));
        }

        return $this->getResponse($request, $curl, $responseHandle, $cookieFile);
    }

    private function initRequest(Request $request, HttpMethod $method = null): CurlHandle
    {
        $method ??= $request->getMethod();
        $port = $request->getPort();
        $url = $request->getUrl();
        $parameters = $request->getParameters();

        try {
            $requestBody = $request->getBody()?->getContent();
        } catch (WebException) {
            $requestBody = null;
        }

        if (count($parameters) > 0) {
            if (!empty($requestBody)) {
                throw new WebException('Request body and parameters are set!');
            }

            $requestBody = http_build_query($parameters);
        }

        if ($method !== HttpMethod::POST && $requestBody !== null) {
            $url .= '?' . $requestBody;
        }

        $this->logger->debug('Call ' . ($method?->value ?? '') . ' ' . $url . '::' . $port);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method?->value);

        $headers = $request->getHeaders();

        if (!empty($requestBody) && $method === HttpMethod::POST) {
            $this->logger->debug('With body: ' . $requestBody);
            $headers['Content-Length'] = (string) strlen($requestBody);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        if (count($headers) > 0) {
            $curlHeaders = [];

            foreach ($headers as $headerKey => $headerValue) {
                $curlHeaders[] = $headerKey . ': ' . $headerValue;
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_PORT, $port);

        return $curl;
    }

    private function setCookieFile(Request $request, CurlHandle $curl): string
    {
        $cookieFile = $request->getCookieFile();

        if ($cookieFile !== null) {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        }

        $cookieFile ??= sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cookies' . uniqid();
        $this->logger->debug('Cookies saved in: ' . $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);

        return $cookieFile;
    }

    /**
     * @param resource $responseHandle
     *
     * @throws WebException
     */
    private function getResponse(
        Request $request,
        CurlHandle $curl,
        $responseHandle,
        string $cookieFile,
    ): Response {
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        rewind($responseHandle);
        $length = fstat($responseHandle)['size'];

        if ($length <= 0) {
            throw new WebException('No response length! Length: ' . $length);
        }

        $this->logger->debug('Get response with length ' . $length);

        return new Response(
            $request,
            HttpStatusCode::from($httpCode),
            $request->getHeaders(),
            (new Body())->setResource($responseHandle, $length),
            $cookieFile
        );
    }
}
