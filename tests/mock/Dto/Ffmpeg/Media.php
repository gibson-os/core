<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Ffmpeg;

use GibsonOS\Core\Dto\Ffmpeg\Media as MediaDto;
use GibsonOS\Mock\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Mock\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Mock\Dto\Ffmpeg\Stream\Video;

class Media
{
    public static function create(string $filename): MediaDto
    {
        return (new MediaDto($filename))
            ->setBitRate(42)
            ->setDuration(42.42)
            ->setFrames(42000)
            ->setAudioStreams([
                'a1' => Audio::create(),
                'a2' => Audio::create(),
                'a3' => Audio::create(),
            ])
            ->setSubtitleStreams([
                's1' => Subtitle::create(),
                's2' => Subtitle::create(),
                's3' => Subtitle::create(),
                's4' => Subtitle::create(),
                's5' => Subtitle::create(),
            ])
            ->setVideoStreams([
                'v1' => Video::create(),
                'v2' => Video::create(),
            ])
        ;
    }
}
