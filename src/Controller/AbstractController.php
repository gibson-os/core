<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\ControllerError;
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

    protected function checkPermission(int $permission): void
    {
        //$this->permissionService->hasPermission($permission)
    }

    /**
     * @throws ControllerError
     */
    protected function renderTemplate(string $templatePath, array $context = []): Response
    {
        $twig = $this->twigService->getTwig();
        $context['baseDir'] = preg_replace('|^(.*/).+?$|', '$1', $_SERVER['SCRIPT_NAME']);
        $context['domain'] = strtolower($_SERVER['REQUEST_SCHEME']) . '://' . $_SERVER['HTTP_HOST'];
        $now = time();
        $context['serverDate'] = [
            'now' => $now,
            'sunrise' => date_sunrise($now, SUNFUNCS_RET_TIMESTAMP),
            'sunset' => date_sunset($now, SUNFUNCS_RET_TIMESTAMP),
        ];
        $context['request'] = $this->requestService;
        $context['session'] = $this->sessionService;

        try {
            return new Response(
                $twig->render($templatePath, $context),
                StatusCode::OK,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            throw new ControllerError('Template render error!', 0, $e);
        }
    }

    protected function returnSuccess($data): AjaxResponse
    {
        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => $data,
        ]);
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
