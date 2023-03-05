<?php
declare(strict_types=1);

namespace GibsonOS\Core\Enum\Middleware\Message;

enum Vibrate: string
{
    case SOS = 'SOS';

    /**
     * @return int[]
     */
    public function getPattern(): array
    {
        return match ($this) {
            self::SOS => [0, 300, 150, 300, 150, 300, 300, 600, 150, 600, 150, 600, 300, 300, 150, 300, 150, 300],
        };
    }
}
