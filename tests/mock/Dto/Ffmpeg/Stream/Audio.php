<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Ffmpeg\Stream;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio as AudioDto;

class Audio
{
    public static function create(): AudioDto
    {
        return (new AudioDto())
            ->setBitrate('bitrate')
            ->setChannels('channels')
            ->setDefault(false)
            ->setFormat('format')
            ->setFrequency('frequency')
            ->setLanguage('language')
        ;
    }
}
