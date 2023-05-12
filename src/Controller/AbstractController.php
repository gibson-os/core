<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;

abstract class AbstractController
{
    public function __construct(
        protected RequestService $requestService,
        protected TwigService $twigService,
        protected SessionService $sessionService
    ) {
    }

    protected function renderTemplate(string $templatePath, array $context = []): TwigResponse
    {
        return (new TwigResponse($this->twigService, $templatePath))
            ->setVariables($context)
        ;
    }

    protected function returnSuccess($data = null, int $total = null): AjaxResponse
    {
        $return = [
            'success' => true,
            'failure' => false,
            'data' => is_iterable($data) && !is_array($data) ? iterator_to_array($data) : $data,
        ];

        if ($total !== null) {
            $return['total'] = $total;
        }

        return new AjaxResponse($return);
    }

    protected function returnFailure($message, HttpStatusCode $code = HttpStatusCode::BAD_REQUEST): AjaxResponse
    {
        return new AjaxResponse([
            'success' => false,
            'failure' => true,
            'msg' => $message,
        ], $code);
    }
}
