<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Dto\Parameter\ActionParameter;
use GibsonOS\Core\Dto\Parameter\ModuleParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Dto\Parameter\TaskParameter;
use GibsonOS\Core\Dto\Parameter\UserParameter;
use GibsonOS\Core\Exception\FcmException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\FcmService;

#[Event('Firebase cloud messaging')]
class FcmEvent extends AbstractEvent
{
    #[Event\Trigger('Vor dem senden einer Nachricht')]
    public const TRIGGER_BEFORE_PUSH_MESSAGE = 'beforePushMessage';

    #[Event\Trigger('Nach dem senden einer Nachricht')]
    public const TRIGGER_AFTER_PUSH_MESSAGE = 'afterPushMessage';

    public function __construct(
        EventService $eventService,
        ReflectionManager $reflectionManager,
        private readonly FcmService $fcmService,
    ) {
        parent::__construct($eventService, $reflectionManager);
    }

    /**
     * @throws FcmException
     * @throws WebException
     * @throws \JsonException
     */
    #[Event\Method('Nachricht senden')]
    public function pushMessage(
        #[Event\Parameter(UserParameter::class)] User $user,
        #[Event\Parameter(ModuleParameter::class)] Module $module,
        #[Event\Parameter(TaskParameter::class)] Task $task,
        #[Event\Parameter(ActionParameter::class)] Action $action,
        #[Event\Parameter(StringParameter::class, 'Titel')] ?string $title,
        #[Event\Parameter(StringParameter::class, 'Text')] ?string $body,
    ): void {
        foreach ($user->getDevices() as $device) {
            $token = $device->getToken();
            $fcmToken = $device->getFcmToken();

            if ($token === null || $fcmToken === null) {
                continue;
            }

            $this->fcmService->pushMessage(new Message(
                $token,
                $fcmToken,
                title: $title,
                body: $body,
                module: $module->getName(),
                task: $task->getName(),
                action: $action->getName()
            ));
        }
    }
}
