<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Event\FcmEvent;
use GibsonOS\Core\Exception\FcmException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Utility\StatusCode;

class FcmService
{
    private const URL = 'https://fcm.googleapis.com/v1/projects/';

    private string $url;

    public function __construct(
        private readonly MiddlewareService $middlewareService,
        private readonly EventService $eventService,
        private readonly DeviceRepository $deviceRepository,
    ) {
    }

    /**
     * @throws WebException
     * @throws FcmException
     * @throws \JsonException
     */
    public function pushMessage(Message $message): FcmService
    {
        $this->eventService->fire(FcmEvent::class, FcmEvent::TRIGGER_BEFORE_PUSH_MESSAGE);
        $response = $this->middlewareService->send('message', 'push', body: JsonUtility::encode($message));
        $this->eventService->fire(FcmEvent::class, FcmEvent::TRIGGER_AFTER_PUSH_MESSAGE);

        if ($response->getStatusCode() === StatusCode::NOT_FOUND) {
            $this->deviceRepository->removeFcmToken($message->getFcmToken());
        }

        return $this;
    }
}
