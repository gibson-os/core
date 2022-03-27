<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Dto\Fcm\Message\Priority;
use GibsonOS\Core\Dto\Fcm\Message\Type;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use JsonSerializable;

class Message implements JsonSerializable
{
    public const OPTION_NONE = 0;

    public const OPTION_VIBRATION = 1;

    public const OPTION_SOUND = 2;

    public function __construct(
        private string $token,
        private string $fcmToken,
        private Type $type = Type::NOTIFICATION,
        private ?string $title = null,
        private ?string $body = null,
        private string $module = 'core',
        private string $task = 'desktop',
        private string $action = 'index',
        private array $data = [],
        private Priority $priority = Priority::NORMAL,
        private int $options = self::OPTION_VIBRATION + self::OPTION_SOUND
    ) {
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        $data = [
            'token' => $this->fcmToken,
            'android' => [
                'priority' => $this->priority->value,
            ],
        ];

        if ($this->title === null || $this->body !== null) {
            $data['notification'] = [
                'title' => $this->title,
                'body' => $this->body,
            ];
        }

        $data['data'] = [
            'token' => $this->token,
            'type' => $this->type->value,
            'module' => $this->module,
            'task' => $this->task,
            'action' => $this->action,
            'options' => (string) $this->options,
        ];

        if (count($this->data)) {
            $data['data']['payload'] = JsonUtility::encode($this->data);
        }

        return $data;
    }
}
