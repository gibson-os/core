<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Utility\StatusCode;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractController
{
    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * @var RequestService
     */
    protected $requestService;

    /**
     * @var TwigService
     */
    protected $twigService;

    /**
     * @var SessionService
     */
    protected $sessionService;

    public function __construct(
        PermissionService $permissionService,
        RequestService $requestService,
        TwigService $twigService,
        SessionService $sessionService
    ) {
        $this->permissionService = $permissionService;
        $this->requestService = $requestService;
        $this->twigService = $twigService;
        $this->sessionService = $sessionService;
    }

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    protected function checkPermission(int $permission): void
    {
        $hasPermission = $this->permissionService->hasPermission(
            $permission,
            $this->requestService->getModuleName(),
            $this->requestService->getTaskName(),
            $this->requestService->getActionName(),
            $this->sessionService->getUserId()
        );

        if ($hasPermission) {
            return;
        }

        if ($this->sessionService->isLogin()) {
            throw new PermissionDenied();
        }

        throw new LoginRequired();
    }

    /**
     * @throws ControllerError
     */
    protected function renderTemplate(string $templatePath, array $context = []): Response
    {
        try {
            return new Response(
                $this->twigService->getTwig()->render($templatePath, $context),
                StatusCode::OK,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw new ControllerError('Template render error!', 0, $e);
        }
    }

    protected function returnSuccess($data = null, int $total = null): AjaxResponse
    {
        $return = [
            'success' => true,
            'failure' => false,
            'data' => $data,
        ];

        if ($total !== null) {
            $return['total'] = $total;
        }

        return new AjaxResponse($return);
    }

    protected function returnFailure($message, int $code = 400): AjaxResponse
    {
        return new AjaxResponse([
            'success' => false,
            'failure' => true,
            'msg' => $message,
        ], $code);
    }
}
