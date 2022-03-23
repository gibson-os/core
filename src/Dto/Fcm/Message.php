<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Dto\Fcm\Message\Type;
use JsonSerializable;

class Message implements JsonSerializable
{
    public const OPTION_VIBRATION = 1;

    public const OPTION_SOUND = 2;

    public function __construct(
        private string $fcmToken,
        private Type $type = Type::NOTIFICATION,
        private ?string $title = null,
        private ?string $body = null,
        private string $module = 'core',
        private string $task = 'index',
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
            'type' => $this->type->value,
            'module' => $this->module,
            'task' => $this->task,
            'action' => $this->action,
            'options' => $this->options,
        ];

        if (count($this->data)) {
            $data['data']['payload'] = $data;
        }

        return $data;
    }
}
