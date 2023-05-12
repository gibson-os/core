<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\WebException;
use Psr\Log\LoggerInterface;

class WebService
{
    private const METHOD_GET = 'GET';

    private const METHOD_POST = 'POST';

    private const METHOD_HEAD = 'HEAD';

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @throws WebException
     */
    public function get(Request $request): Response
    {
        return $this->request($request, self::METHOD_GET);
    }

    /**
     * @throws WebException
     */
    public function post(Request $request): Response
    {
        return $this->request($request, self::METHOD_POST);
    }

    /**
     * @throws WebException
     */
    public function head(Request $request): Response
    {
        return $this->request($request, self::METHOD_HEAD);
    }

    /**
     * @throws WebException
     */
    private function request(Request $request, string $method): Response
    {
        $responseHandle = fopen('php://memory', 'r+');

        $port = $request->getPort();
        $url = $request->getUrl();
        $this->logger->debug('Call ' . $method . ' ' . $url . '::' . $port);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $parameters = $request->getParameters();

        try {
            $requestBody = $request->getBody()?->getContent();
        } catch (WebException) {
            $requestBody = null;
        }

        $headers = $request->getHeaders();

        if (count($parameters) > 0) {
            if (!empty($requestBody)) {
                throw new WebException('Request body and parameters are set!');
            }

            $requestBody = http_build_query($parameters);
        }

        if (!empty($requestBody)) {
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
        curl_setopt($curl, CURLOPT_FILE, $responseHandle);

        $cookieFile = $request->getCookieFile();

        if ($cookieFile !== null) {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        }

        $cookieFile ??= sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cookies' . uniqid();
        $this->logger->debug('Cookies saved in: ' . $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);

        if (!curl_exec($curl)) {
            throw new WebException(curl_error($curl));
        }

        rewind($responseHandle);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $length = fstat($responseHandle)['size'];

        if ($length <= 0) {
            throw new WebException('No response length! Length: ' . $length);
        }

        $this->logger->debug('Get response with length ' . $length);

        return new Response(
            $request,
            HttpStatusCode::from($httpCode),
            $headers,
            (new Body())->setResource($responseHandle, $length),
            $cookieFile
        );
    }
}
