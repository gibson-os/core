<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Dto\Fcm\Message\Priority;
use GibsonOS\Core\Dto\Fcm\Message\Type;
use GibsonOS\Core\Utility\JsonUtility;

class Message implements \JsonSerializable
{
    public const OPTION_NONE = 0;

    public const OPTION_VIBRATION = 1;

    public const OPTION_SOUND = 2;

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
        private readonly int $options = self::OPTION_VIBRATION + self::OPTION_SOUND
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
            'options' => (string) $this->options,
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
