<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use Psr\Log\LoggerInterface;

class WebService extends AbstractService
{
    private const METHOD_GET = 'GET';

    private const METHOD_POST = 'POST';

    private const METHOD_HEAD = 'HEAD';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
     * @throws JsonException
     */
    private function request(Request $request, string $method): Response
    {
        $responseHandle = fopen('php://memory', 'r+');
        $headers = [];

        $port = $request->getPort();
        $url = $request->getUrl();
        $this->logger->debug('Call ' . $method . ' ' . $url . '::' . $port);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $parameters = $request->getParameters();

        if (!empty($parameters)) {
            $this->logger->debug('With parameters: ' . JsonUtility::encode($parameters));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
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
        curl_close($curl);
        $length = fstat($responseHandle)['size'];

        if ($length <= 0) {
            throw new WebException('No response length! Length: ' . $length);
        }

        $this->logger->debug('Get response with length ' . $length);

        return new Response(
            $request,
            $headers,
            (new Body())->setResource($responseHandle, $length),
            $cookieFile
        );
    }
}
