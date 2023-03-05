<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Enum\Middleware\Message\Priority;
use GibsonOS\Core\Enum\Middleware\Message\Type;
use GibsonOS\Core\Enum\Middleware\Message\Vibrate;
use GibsonOS\Core\Utility\JsonUtility;

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
        $data = [
            'token' => $this->fcmToken,
            'android' => [
                'priority' => $this->priority->value,
            ],
        ];

        $data['data'] = [
            'token' => $this->token,
            'type' => $this->type->value,
            'module' => $this->module,
            'task' => $this->task,
            'action' => $this->action,
            'vibrate' => JsonUtility::encode($this->vibrate?->getPattern() ?? []),
            'title' => $this->title,
            'body' => $this->body,
        ];

        if (count($this->data)) {
            $data['data']['payload'] = JsonUtility::encode($this->data, JSON_THROW_ON_ERROR);
        }

        return $data;
    }

    public function getFcmToken(): string
    {
        return $this->fcmToken;
    }
}
