<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Dto\Fcm\Message\Type;
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
        private int $options = self::OPTION_VIBRATION + self::OPTION_SOUND
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = ['token' => $this->fcmToken];

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
            $data['data']['payload'] = $data;
        }

        return $data;
    }
}
