<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\WebException;

class WebService extends AbstractService
{
    private const METHOD_GET = 'GET';

    private const METHOD_POST = 'POST';

    private const METHOD_HEAD = 'HEAD';

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
        $headers = [];

        $curl = curl_init($request->getUrl());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($request->getParameters())) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request->getParameters());
        }

        curl_setopt($curl, CURLOPT_PORT, $request->getPort());
        curl_setopt($curl, CURLOPT_FILE, $responseHandle);

        $cookieFile = $request->getCookieFile();

        if ($cookieFile !== null) {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        }

        $cookieFile ??= sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cookies' . uniqid();
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);

        if (!curl_exec($curl)) {
            throw new WebException(curl_error($curl));
        }

        rewind($responseHandle);

        return new Response(
            $request,
            $headers,
            (new Body())
                ->setResource($responseHandle)
                ->setLength((int) curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD)),
            $cookieFile
        );
    }
}
