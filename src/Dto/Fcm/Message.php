<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Enum\Middleware\Message\Priority;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Core\Enum\Middleware\Message\Vibrate;

class Message implements \JsonSerializable
{
    public function __construct(
        private readonly string $token,
        private readonly string $fcmToken,
        private readonly Type $type = Type::NOTIFICATION,
        private readonly ?string $title = null,
        private readonly ?string $body = null,
        private readonly string $module = 'core',
        private readonly string $task = 'desktop',
        private readonly string $action = 'index',
        private readonly array $data = [],
        private readonly Priority $priority = Priority::NORMAL,
        private readonly ?Vibrate $vibrate = null,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function jsonSerialize(): array
    {
        return [
            'action' => $this->action,
            'task' => $this->task,
            'module' => $this->module,
            'token' => $this->token,
            'fcmToken' => $this->getFcmToken(),
            'type' => $this->type->value,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'priority' => $this->priority->value,
            'vibrate' => $this->vibrate?->value ?? null,
        ];
    }

    public function getFcmToken(): string
    {
        return $this->fcmToken;
    }
}
