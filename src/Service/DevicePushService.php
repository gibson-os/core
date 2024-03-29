<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Enum\Middleware\Message\Priority;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Core\Repository\DevicePushRepository;

class DevicePushService
{
    public function __construct(
        private readonly DevicePushRepository $devicePushRepository,
        private readonly FcmService $fcmService,
    ) {
    }

    public function push(string $module, string $task, string $action, string $foreignId, array $payload): void
    {
        foreach ($this->devicePushRepository->getAllByAction($module, $task, $action, $foreignId) as $devicePush) {
            $token = $devicePush->getDevice()->getToken();
            $fcmToken = $devicePush->getDevice()->getFcmToken();

            if ($token === null || $fcmToken === null) {
                continue;
            }

            $this->fcmService->pushMessage(new Message(
                $token,
                $fcmToken,
                Type::UPDATE,
                module: $module,
                task: $task,
                action: $action,
                data: $payload,
                priority: Priority::HIGH,
            ));
        }
    }
}
