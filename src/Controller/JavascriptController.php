<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Service\JavascriptService;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Utility\StatusCode;

class JavascriptController extends AbstractController
{
    public function index(JavascriptService $javaScriptService, string $module = 'all', string $task = 'all'): Response
    {
        $userId = $this->sessionService->getUserId();

        if ($module === 'all') {
            return $this->getRepsonse($javaScriptService->getByUserId($userId));
        }

        if ($task === 'all') {
            return $this->getRepsonse($javaScriptService->getByUserId($userId, $module));
        }

        return $this->getRepsonse($javaScriptService->getByUserIdAndTask($userId, $module, $task));
    }

    private function getRepsonse(string $body): Response
    {
        return new Response($body, StatusCode::OK, ['Content-Type' => ' application/javascript; charset=UTF-8']);
    }
}
