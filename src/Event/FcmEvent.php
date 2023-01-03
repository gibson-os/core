<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event;

use GibsonOS\Core\Attribute\Event;

#[Event('Firebase cloud messaging')]
#[Event\Listener('task', 'module', ['params' => [
    'paramKey' => 'moduleId',
    'recordKey' => 'id',
]])]
#[Event\Listener('action', 'task', ['params' => [
    'paramKey' => 'taskId',
    'recordKey' => 'id',
]])]
class FcmEvent extends AbstractEvent
{
    #[Event\Trigger('Vor dem senden einer Nachricht')]
    public const TRIGGER_BEFORE_PUSH_MESSAGE = 'beforePushMessage';

    #[Event\Trigger('Nach dem senden einer Nachricht')]
    public const TRIGGER_AFTER_PUSH_MESSAGE = 'afterPushMessage';
}
