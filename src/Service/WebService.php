<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\WebError;

class WebService extends AbstractService
{
    private const METHOD_GET = 'GET';

    private const METHOD_POST = 'POST';

    private const METHOD_HEAD = 'HEAD';

    public function get(Request $request): Response
    {
        return $this->request($request, self::METHOD_GET);
    }

    public function post(Request $request): Response
    {
        return $this->request($request, self::METHOD_POST);
    }

    public function head(Request $request): Response
    {
        return $this->request($request, self::METHOD_HEAD);
    }

    /**
     * @throws WebError
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

        if (!curl_exec($curl)) {
            throw new WebError(curl_error($curl));
        }

        rewind($responseHandle);

        return new Response($request, $headers, $responseHandle);
    }
}
