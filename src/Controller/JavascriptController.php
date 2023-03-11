<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\JavascriptService;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Utility\StatusCode;

class JavascriptController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws GetError
     */
    public function index(
        JavascriptService $javaScriptService,
        string $module = 'all',
        string $task = 'all',
        bool $withDefault = true,
    ): Response {
        $userId = $this->sessionService->getUserId();

        if ($module === 'all') {
            return $this->getResponse($javaScriptService->getByUserId($userId, withDefault: $withDefault));
        }

        if ($task === 'all') {
            return $this->getResponse($javaScriptService->getByUserId($userId, $module, $withDefault));
        }

        return $this->getResponse($javaScriptService->getByUserIdAndTask($userId, $module, $task, $withDefault));
    }

    private function getResponse(string $body): Response
    {
        return new Response($body, StatusCode::OK, ['Content-Type' => ' application/javascript; charset=UTF-8']);
    }
}
