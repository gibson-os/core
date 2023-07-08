<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use InvalidArgumentException;
use JsonException;

class MiddlewareService
{
    private Setting $middlewareToken;

    private readonly Setting $middlewareSecret;

    /**
     * @throws SelectError
     */
    public function __construct(
        #[GetEnv('MIDDLEWARE_URL')] private readonly ?string $middlewareUrl,
        #[GetEnv('WEB_URL')] private readonly string $webUrl,
        private readonly WebService $webService,
        private readonly ModelManager $modelManager,
        private readonly SettingRepository $settingRepository,
        ModuleRepository $moduleRepository,
        #[GetSetting('middlewareToken', 'core')] Setting $middlewareToken = null,
        #[GetSetting('middlewareSecret', 'core')] Setting $middlewareSecret = null,
    ) {
        $module = $moduleRepository->getByName('core');
        $this->middlewareToken = $middlewareToken
            ?? (new Setting())
                ->setModule($module)
                ->setKey('middlewareToken')
        ;
        $this->middlewareSecret = $middlewareSecret
            ?? (new Setting())
                ->setModule($module)
                ->setKey('middlewareSecret')
        ;
    }

    /**
     * @throws MiddlewareException
     * @throws SaveError
     * @throws WebException
     * @throws JsonException
     */
    public function send(string $task, string $action, array $parameters = [], string $body = null, HttpMethod $method = null): Response
    {
        if ($this->middlewareUrl === null) {
            throw new InvalidArgumentException('Middleware URL not set');
        }

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

        $response = match ($method) {
            HttpMethod::POST => $this->webService->post($request),
            HttpMethod::GET => $this->webService->get($request),
            default => (count($parameters) || $body !== null)
                ? $this->webService->post($request)
                : $this->webService->get($request)
        };

        if ($response->getStatusCode() === HttpStatusCode::UNAUTHORIZED) {
            $this->getNewToken();
            $request->setHeader('X-GibsonOs-Token', $this->middlewareToken->getValue());

            return $this->checkResponse($request, $this->webService->get($request));
        }

        return $this->checkResponse($request, $response);
    }

    /**
     * @throws MiddlewareException
     * @throws WebException
     */
    private function checkResponse(Request $request, Response $response): Response
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode->value < HttpStatusCode::OK->value || $statusCode->value > HttpStatusCode::PERMANENT_REDIRECT->value) {
            throw new MiddlewareException(sprintf(
                'Response error! URL %s. Code %d. Response: %s',
                $request->getUrl(),
                $statusCode->value,
                $response->getBody()->getContent(),
            ));
        }

        return $response;
    }

    /**
     * @throws MiddlewareException
     * @throws WebException
     * @throws SaveError
     * @throws JsonException
     */
    private function getNewToken(): void
    {
        if ($this->middlewareUrl === null || $this->middlewareUrl === '' || $this->middlewareUrl === '/') {
            throw new InvalidArgumentException('Middleware URL not set');
        }

        $this->modelManager->saveWithoutChildren(
            $this->middlewareSecret->setValue(mb_substr(base64_encode(random_bytes(190)), 0, 256))
        );
        $response = $this->webService->post(
            (new Request(sprintf('%smiddleware/instance/newToken', $this->middlewareUrl)))
                ->setParameters([
                    'url' => $this->webUrl,
                    'secret' => $this->middlewareSecret->getValue(),
                ])
                ->setHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        );

        if ($response->getStatusCode() !== HttpStatusCode::OK) {
            throw new MiddlewareException(sprintf(
                'Response error. Code %d. Response: %s',
                $response->getStatusCode()->value,
                $response->getBody()->getContent(),
            ));
        }

        $this->middlewareToken = $this->settingRepository->getByKeyAndModuleName(
            'core',
            null,
            'middlewareToken'
        );
    }
}
