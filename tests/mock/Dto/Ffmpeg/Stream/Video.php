<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Ffmpeg\Stream;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Video as VideoDto;

class Video
{
    public static function create(): VideoDto
    {
        return (new VideoDto())
            ->setDefault(false)
            ->setLanguage('language')
            ->setCodec('codec')
            ->setColorSpace('colorSpace')
            ->setFps(42)
            ->setHeight(1080)
            ->setWidth(1920)
        ;
    }
}
