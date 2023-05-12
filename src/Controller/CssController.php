<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\CssService;
use GibsonOS\Core\Service\Response\Response;

class CssController extends AbstractController
{
    /**
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     */
    public function getIndex(CssService $cssService, string $module = 'all', string $task = 'all'): Response
    {
        $userId = $this->sessionService->getUserId();

        if ($module === 'all') {
            return $this->getResponse($cssService->getByUserId($userId));
        }

        if ($task === 'all') {
            return $this->getResponse($cssService->getByUserId($userId, $module));
        }

        return $this->getResponse($cssService->getByUserIdAndTask($userId, $module, $task));
    }

    private function getResponse(string $body): Response
    {
        return new Response($body, headers: ['Content-Type' => ' text/css; charset=UTF-8']);
    }
}
