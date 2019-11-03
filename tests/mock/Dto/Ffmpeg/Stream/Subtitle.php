<?php
declare(strict_types=1);

namespace GibsonOS\Mock\Dto\Ffmpeg\Stream;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle as SubtitleDto;

class Subtitle
{
    public static function create(): SubtitleDto
    {
        return (new SubtitleDto())
            ->setLanguage('language')
            ->setDefault(false)
            ->setForced(false)
        ;
    }
}
