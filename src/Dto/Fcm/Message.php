<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm;

use GibsonOS\Core\Dto\Fcm\Message\Type;
use JsonSerializable;

class Message implements JsonSerializable
{
    public function __construct(
        private string $fcmToken,
        private Type $type = Type::NOTIFICATION,
        private $timeToLive = 600,
        private ?string $title = null,
        private ?string $body = null,
        private array $data = []
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = [
            'token' => $this->fcmToken,
//            'type' => $this->type->value,
            'time_to_live' => $this->timeToLive,
        ];

        if ($this->title === null || $this->body !== null) {
            $data['notification'] = [
                'title' => $this->title,
                'body' => $this->body,
            ];
        }

        if (count($this->data)) {
            $data['data'] = $data;
        }

        return $data;
    }
}
