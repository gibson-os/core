<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;

class MiddlewareService
{
    private readonly Setting $middlewareToken;

    /**
     * @throws SelectError
     */
    public function __construct(
        #[GetEnv('MIDDLEWARE_URL')] private readonly string $middlewareUrl,
        #[GetEnv('WEB_URL')] private readonly string $webUrl,
        private readonly WebService $webService,
        private readonly ModelManager $modelManager,
        ModuleRepository $moduleRepository,
        #[GetSetting('middlewareToken', 'core')] Setting $middlewareToken = null,
    ) {
        $this->middlewareToken = $middlewareToken
            ?? (new Setting())
                ->setModule($moduleRepository->getByName('core'))
                ->setKey('token')
        ;
    }

    /**
     * @throws MiddlewareException
     * @throws SaveError
     * @throws WebException
     * @throws \JsonException
     */
    public function send(string $task, string $action, array $parameters = [], string $body = null): Response
    {
        if ($this->middlewareToken->getId() === null) {
            $this->getNewToken();
        }

        $request = (new Request(sprintf('%smiddleware/%s/%s', $this->middlewareUrl, $task, $action)))
            ->setHeaders([
                'X-GibsonOs-Token' => $this->middlewareToken->getValue(),
                'X-Requested-With' => 'XMLHttpRequest',
            ])
        ;

        if (count($parameters)) {
            $request->setParameters($parameters);
        }

        if ($body !== null) {
            $request->setBody((new Body())->setContent($body, strlen($body)));
        }

        if (count($parameters) || $body !== null) {
            $response = $this->webService->post($request);
        } else {
            $response = $this->webService->get($request);
        }

        if ($response->getStatusCode() === StatusCode::UNAUTHORIZED) {
            $this->getNewToken();
            $request->setHeader('X-GibsonOs-Token', $this->middlewareToken->getValue());

            return $this->checkResponse($request, $this->webService->get($request));
        }

        return $this->checkResponse($request, $response);
    }

    private function checkResponse(Request $request, Response $response): Response
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode < StatusCode::OK || $statusCode > StatusCode::PERMANENT_REDIRECT) {
            throw new MiddlewareException(sprintf(
                'Response error! URL %s. Code %d. Response: %s',
                $request->getUrl(),
                $statusCode,
                $response->getBody()->getContent(),
            ));
        }

        return $response;
    }

    /**
     * @throws MiddlewareException
     * @throws WebException
     * @throws SaveError
     * @throws \JsonException
     */
    private function getNewToken(): void
    {
        $response = $this->webService->post(
            (new Request(sprintf('%smiddleware/instance/newToken', $this->middlewareUrl)))
                ->setParameters(['url' => $this->webUrl])
                ->setHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        );

        try {
            $content = $response->getBody()->getContent();
        } catch (WebException) {
            $content = '';
        }

        if ($response->getStatusCode() !== StatusCode::OK) {
            throw new MiddlewareException(sprintf(
                'Response error. Code %d. Response: %s',
                $response->getStatusCode(),
                $content,
            ));
        }

        $instance = JsonUtility::decode($content)['data'];
        $this->modelManager->saveWithoutChildren($this->middlewareToken->setValue($instance['token']));
    }
}
