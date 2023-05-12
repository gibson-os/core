<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Event\FcmEvent;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Repository\User\DeviceRepository;
use JsonException;

class FcmService
{
    public function __construct(
        private readonly MiddlewareService $middlewareService,
        private readonly EventService $eventService,
        private readonly DeviceRepository $deviceRepository,
    ) {
    }

    /**
     * @throws JsonException
     * @throws WebException
     * @throws MiddlewareException
     * @throws SaveError
     */
    public function pushMessage(Message $message): FcmService
    {
        $this->eventService->fire(FcmEvent::class, FcmEvent::TRIGGER_BEFORE_PUSH_MESSAGE);
        $response = $this->middlewareService->send('message', 'push', $message->jsonSerialize());
        $this->eventService->fire(FcmEvent::class, FcmEvent::TRIGGER_AFTER_PUSH_MESSAGE);

        if ($response->getStatusCode() === HttpStatusCode::NOT_FOUND) {
            $this->deviceRepository->removeFcmToken($message->getFcmToken());
        }

        return $this;
    }
}
