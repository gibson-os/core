<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Fcm\Message;

enum Vibrate: string
{
    case SOS = 'SOS';

    /**
     * @return int[]
     */
    public function getPattern(): array
    {
        return match ($this) {
            self::SOS => [500, 100, 500, 100, 500, 100, 1000, 100, 1000, 100, 1000, 100, 500, 100, 500, 100, 500]
        };
    }
}
